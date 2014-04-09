<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\UriSigner;
use Liip\ImagineBundle\ImagineEvents;
use Liip\ImagineBundle\Events\CacheResolveEvent;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\CacheManager
 */
class CacheManagerTest extends AbstractTest
{
    protected $resolver;

    public function testAddCacheManagerAwareResolver()
    {
        $cacheManager = new CacheManager($this->createFilterConfigurationMock(), $this->createRouterMock(), new UriSigner('secret'), $this->createEventDispatcherMock());

        $resolver = $this->getMock('Liip\ImagineBundle\Tests\Fixtures\CacheManagerAwareResolver');
        $resolver
            ->expects($this->once())
            ->method('setCacheManager')
            ->with($cacheManager)
        ;

        $cacheManager->addResolver('thumbnail', $resolver);
    }

    public function testGetBrowserPathWithoutResolver()
    {
        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'cache' => null,
            )))
        ;

        $cacheManager = new CacheManager($config, $this->createRouterMock(), new UriSigner('secret'), $this->createEventDispatcherMock());

        $this->setExpectedException('OutOfBoundsException', 'Could not find resolver "default" for "thumbnail" filter type');
        $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail');
    }

    public function testDefaultResolverUsedIfNoneSetOnGetBrowserPath()
    {
        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('isStored')
            ->with('cats.jpeg', 'thumbnail')
            ->will($this->returnValue(true))
        ;
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('cats.jpeg', 'thumbnail')
            ->will($this->returnValue('http://a/path/to/an/image.png'))
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->exactly(2))
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'cache' => null,
            )))
        ;

        $router = $this->createRouterMock();
        $router
            ->expects($this->never())
            ->method('generate')
        ;

        $dispatcher = $this->createEventDispatcherMock();
        $dispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(ImagineEvents::PRE_RESOLVE, new CacheResolveEvent('cats.jpeg', 'thumbnail'))
            ->will($this->returnArgument(1));
        $dispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(ImagineEvents::POST_RESOLVE, new CacheResolveEvent('cats.jpeg', 'thumbnail', 'http://a/path/to/an/image.png'))
            ->will($this->returnArgument(1));

        $cacheManager = new CacheManager($config, $router, new UriSigner('secret'), $dispatcher);
        $cacheManager->addResolver('default', $resolver);

        $actualBrowserPath = $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail');

        $this->assertEquals('http://a/path/to/an/image.png', $actualBrowserPath);
    }

    public function testFilterActionUrlGeneratedAndReturnIfResolverReturnNullOnGetBrowserPath()
    {
        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('isStored')
            ->with('cats.jpeg', 'thumbnail')
            ->will($this->returnValue(false))
        ;
        $resolver
            ->expects($this->never())
            ->method('resolve')
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'cache' => null,
            )))
        ;

        $router = $this->createRouterMock();
        $router
            ->expects($this->once())
            ->method('generate')
            ->will($this->returnValue('/media/cache/thumbnail/cats.jpeg'))
        ;

        $cacheManager = new CacheManager($config, $router, new UriSigner('secret'), $this->createEventDispatcherMock());
        $cacheManager->addResolver('default', $resolver);

        $actualBrowserPath = $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail');

        $this->assertEquals('/media/cache/thumbnail/cats.jpeg', $actualBrowserPath);
    }

    /**
     * @dataProvider invalidPathProvider
     */
    public function testResolveInvalidPath($path)
    {
        $cacheManager = new CacheManager($this->createFilterConfigurationMock(), $this->createRouterMock(), new UriSigner('secret'), $this->createEventDispatcherMock());

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $cacheManager->resolve($path, 'thumbnail');
    }

    public function testThrowsIfConcreteResolverNotExists()
    {
        $dispatcher = $this->createEventDispatcherMock();
        $dispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(ImagineEvents::PRE_RESOLVE, new CacheResolveEvent('cats.jpeg', 'thumbnail'))
            ->will($this->returnArgument(1));

        $cacheManager = new CacheManager($this->createFilterConfigurationMock(), $this->createRouterMock(), new UriSigner('secret'), $dispatcher);

        $this->setExpectedException('OutOfBoundsException', 'Could not find resolver "default" for "thumbnail" filter type');
        $this->assertFalse($cacheManager->resolve('cats.jpeg', 'thumbnail'));
    }

    public function testFallbackToDefaultResolver()
    {
        $binary = new Binary('aContent', 'image/png', 'png');

        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('cats.jpeg', 'thumbnail')
            ->will($this->returnValue('/thumbs/cats.jpeg'))
        ;
        $resolver
            ->expects($this->once())
            ->method('store')
            ->with($binary, '/thumbs/cats.jpeg', 'thumbnail')
        ;
        $resolver
            ->expects($this->once())
            ->method('remove')
            ->with(array('/thumbs/cats.jpeg'), array('thumbnail'))
            ->will($this->returnValue(true))
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->exactly(3))
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'cache' => null,
            )))
        ;

        $dispatcher = $this->createEventDispatcherMock();
        $dispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(ImagineEvents::PRE_RESOLVE, new CacheResolveEvent('cats.jpeg', 'thumbnail'))
            ->will($this->returnArgument(1));
        $dispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(ImagineEvents::POST_RESOLVE, new CacheResolveEvent('cats.jpeg', 'thumbnail', '/thumbs/cats.jpeg'))
            ->will($this->returnArgument(1));

        $cacheManager = new CacheManager(
            $config,
            $this->createRouterMock(),
            new UriSigner('secret'),
            $dispatcher
        );
        $cacheManager->addResolver('default', $resolver);

        // Resolve fallback to default resolver
        $this->assertEquals('/thumbs/cats.jpeg', $cacheManager->resolve('cats.jpeg', 'thumbnail'));

        $cacheManager->store($binary, '/thumbs/cats.jpeg', 'thumbnail');

        // Remove fallback to default resolver
        $cacheManager->remove('/thumbs/cats.jpeg', 'thumbnail');
    }

    public function testGenerateUrl()
    {
        $path = 'thePath';
        $expectedUrl = 'theUrl';

        $routerMock = $this->createRouterMock();
        $routerMock
            ->expects($this->once())
            ->method('generate')
            ->with(
                '_imagine_thumbnail',
                array(
                    'path' => $path
                ),
                true
            )
            ->will($this->returnValue($expectedUrl))
        ;

        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $routerMock,
            new UriSigner('secret'),
            $this->createEventDispatcherMock()
        );

        $this->assertEquals(
            $expectedUrl,
            $cacheManager->generateUrl($path, 'thumbnail')
        );
    }

    public function testRemoveCacheForPathAndFilterOnRemove()
    {
        $expectedPath = 'thePath';
        $expectedFilter = 'theFilter';

        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('remove')
            ->with(array($expectedPath), array($expectedFilter))
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function($filter) {
                return array(
                    'cache' => $filter,
                );
            }))
        ;

        $cacheManager = new CacheManager($config, $this->createRouterMock(), new UriSigner('secret'), $this->createEventDispatcherMock());
        $cacheManager->addResolver($expectedFilter, $resolver);

        $cacheManager->remove($expectedPath, $expectedFilter);
    }

    public function testRemoveCacheForPathAndSomeFiltersOnRemove()
    {
        $expectedPath = 'thePath';
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $resolverOne = $this->createResolverMock();
        $resolverOne
            ->expects($this->once())
            ->method('remove')
            ->with(array($expectedPath), array($expectedFilterOne))
        ;

        $resolverTwo = $this->createResolverMock();
        $resolverTwo
            ->expects($this->once())
            ->method('remove')
            ->with(array($expectedPath), array($expectedFilterTwo))
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function($filter) {
                return array(
                    'cache' => $filter,
                );
            }))
        ;

        $cacheManager = new CacheManager($config, $this->createRouterMock(), new UriSigner('secret'), $this->createEventDispatcherMock());
        $cacheManager->addResolver($expectedFilterOne, $resolverOne);
        $cacheManager->addResolver($expectedFilterTwo, $resolverTwo);

        $cacheManager->remove($expectedPath, array($expectedFilterOne, $expectedFilterTwo));
    }

    public function testRemoveCacheForSomePathsAndFilterOnRemove()
    {
        $expectedPathOne = 'thePathOne';
        $expectedPathTwo = 'thePathTwo';
        $expectedFilter = 'theFilter';

        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('remove')
            ->with(
                array($expectedPathOne, $expectedPathTwo),
                array($expectedFilter)
            )
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function($filter) {
                return array(
                    'cache' => $filter,
                );
            }))
        ;

        $cacheManager = new CacheManager($config, $this->createRouterMock(), new UriSigner('secret'), $this->createEventDispatcherMock());
        $cacheManager->addResolver($expectedFilter, $resolver);

        $cacheManager->remove(array($expectedPathOne, $expectedPathTwo), $expectedFilter);
    }

    public function testRemoveCacheForSomePathsAndSomeFiltersOnRemove()
    {
        $expectedPathOne = 'thePath';
        $expectedPathTwo = 'thePath';
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $resolverOne = $this->createResolverMock();
        $resolverOne
            ->expects($this->once())
            ->method('remove')
            ->with(array($expectedPathOne, $expectedPathTwo), array($expectedFilterOne))
        ;

        $resolverTwo = $this->createResolverMock();
        $resolverTwo
            ->expects($this->once())
            ->method('remove')
            ->with(array($expectedPathOne, $expectedPathTwo), array($expectedFilterTwo))
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function($filter) {
                return array(
                    'cache' => $filter,
                );
            }))
        ;

        $cacheManager = new CacheManager($config, $this->createRouterMock(), new UriSigner('secret'), $this->createEventDispatcherMock());
        $cacheManager->addResolver($expectedFilterOne, $resolverOne);
        $cacheManager->addResolver($expectedFilterTwo, $resolverTwo);

        $cacheManager->remove(
            array($expectedPathOne, $expectedPathTwo),
            array($expectedFilterOne, $expectedFilterTwo)
        );
    }

    public function testRemoveCacheForAllFiltersOnRemove()
    {
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $resolverOne = $this->createResolverMock();
        $resolverOne
            ->expects($this->once())
            ->method('remove')
            ->with(array(), array($expectedFilterOne))
        ;

        $resolverTwo = $this->createResolverMock();
        $resolverTwo
            ->expects($this->once())
            ->method('remove')
            ->with(array(), array($expectedFilterTwo))
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function($filter) {
                return array(
                    'cache' => $filter,
                );
            }))
        ;
        $config
            ->expects($this->once())
            ->method('all')
            ->will($this->returnValue(array(
                $expectedFilterOne => array(),
                $expectedFilterTwo => array(),
            )))
        ;

        $cacheManager = new CacheManager($config, $this->createRouterMock(), new UriSigner('secret'), $this->createEventDispatcherMock());
        $cacheManager->addResolver($expectedFilterOne, $resolverOne);
        $cacheManager->addResolver($expectedFilterTwo, $resolverTwo);

        $cacheManager->remove();
    }

    public function testRemoveCacheForPathAndAllFiltersOnRemove()
    {
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';
        $expectedPath = 'thePath';

        $resolverOne = $this->createResolverMock();
        $resolverOne
            ->expects($this->once())
            ->method('remove')
            ->with(array($expectedPath), array($expectedFilterOne))
        ;

        $resolverTwo = $this->createResolverMock();
        $resolverTwo
            ->expects($this->once())
            ->method('remove')
            ->with(array($expectedPath), array($expectedFilterTwo))
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function($filter) {
                return array(
                    'cache' => $filter,
                );
            }))
        ;
        $config
            ->expects($this->once())
            ->method('all')
            ->will($this->returnValue(array(
                $expectedFilterOne => array(),
                $expectedFilterTwo => array(),
            )))
        ;

        $cacheManager = new CacheManager($config, $this->createRouterMock(), new UriSigner('secret'), $this->createEventDispatcherMock());
        $cacheManager->addResolver($expectedFilterOne, $resolverOne);
        $cacheManager->addResolver($expectedFilterTwo, $resolverTwo);

        $cacheManager->remove($expectedPath);
    }

    public function testAggregateFiltersByResolverOnRemove()
    {
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('remove')
            ->with(array(), array($expectedFilterOne, $expectedFilterTwo))
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function($filter) {
                return array(
                    'cache' => $filter,
                );
            }))
        ;

        $cacheManager = new CacheManager($config, $this->createRouterMock(), new UriSigner('secret'), $this->createEventDispatcherMock());
        $cacheManager->addResolver($expectedFilterOne, $resolver);
        $cacheManager->addResolver($expectedFilterTwo, $resolver);

        $cacheManager->remove(null, array($expectedFilterOne, $expectedFilterTwo));
    }

    public function testPreResolveEvent()
    {
        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'cache' => null,
            )))
        ;

        $dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
        $dispatcher->addListener(ImagineEvents::PRE_RESOLVE, function ($event) {
            $event->setPath('cats.jpg');
        });

        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->equalTo('cats.jpg'), $this->equalTo('thumbnail'))
            ->will($this->returnValue('/thumbnail/cats.jpg'));

        $cacheManager = new CacheManager($config, $this->createRouterMock(), new UriSigner('secret'), $dispatcher);
        $cacheManager->addResolver('default', $resolver);

        $resolvedUrl = $cacheManager->resolve('invalid_path', 'thumbnail');

        $this->assertEquals($resolvedUrl, '/thumbnail/cats.jpg');
    }

    public function testPostResolveEvent()
    {
        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'cache' => null,
            )))
        ;

        $dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
        $dispatcher->addListener(ImagineEvents::POST_RESOLVE, function ($event) {
            $event->setUrl('/thumbnail/cats.jpg');
        });

        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->equalTo('invalid_path'), $this->equalTo('thumbnail'))
            ->will($this->returnValue('/invalid_path/cats.jpg'));

        $cacheManager = new CacheManager($config, $this->createRouterMock(), new UriSigner('secret'), $dispatcher);
        $cacheManager->addResolver('default', $resolver);

        $resolvedUrl = $cacheManager->resolve('invalid_path', 'thumbnail');

        $this->assertEquals($resolvedUrl, '/thumbnail/cats.jpg');
    }
}
