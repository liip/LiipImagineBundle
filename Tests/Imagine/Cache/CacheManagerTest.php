<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Imagine\Cache;

use Liip\ImagineBundle\Events\CacheResolveEvent;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Cache\Signer;
use Liip\ImagineBundle\ImagineEvents;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @covers \Liip\ImagineBundle\Imagine\Cache\CacheManager
 */
class CacheManagerTest extends AbstractTest
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ResolverInterface
     */
    private function createCacheManagerAwareResolverMock()
    {
        return $resolver = $this
            ->getMockBuilder('\Liip\ImagineBundle\Tests\Fixtures\CacheManagerAwareResolver')
            ->getMock();
    }

    public function testAddCacheManagerAwareResolver()
    {
        $cacheManager = new CacheManager($this->createFilterConfigurationMock(), $this->createRouterInterfaceMock(), new Signer('secret'), $this->createEventDispatcherInterfaceMock());

        $resolver = $this->createCacheManagerAwareResolverMock();
        $resolver
            ->expects($this->once())
            ->method('setCacheManager')
            ->with($cacheManager);

        $cacheManager->addResolver('thumbnail', $resolver);
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Could not find resolver "default" for "thumbnail" filter type
     */
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
            )));

        $cacheManager = new CacheManager($config, $this->createRouterInterfaceMock(), new Signer('secret'), $this->createEventDispatcherInterfaceMock());
        $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail');
    }

    public function testGetRuntimePath()
    {
        $config = $this->createFilterConfigurationMock();
        $cacheManager = new CacheManager($config, $this->createRouterInterfaceMock(), new Signer('secret'), $this->createEventDispatcherInterfaceMock());

        $rcPath = $cacheManager->getRuntimePath('image.jpg', array(
            'thumbnail' => array(
                'size' => array(180, 180),
            ),
        ));

        $this->assertEquals('rc/ILfTutxX/image.jpg', $rcPath);
    }

    public function testDefaultResolverUsedIfNoneSetOnGetBrowserPath()
    {
        $resolver = $this->createCacheResolverInterfaceMock();
        $resolver
            ->expects($this->once())
            ->method('isStored')
            ->with('cats.jpeg', 'thumbnail')
            ->will($this->returnValue(true));
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('cats.jpeg', 'thumbnail')
            ->will($this->returnValue('http://a/path/to/an/image.png'));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->exactly(2))
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'cache' => null,
            )));

        $router = $this->createRouterInterfaceMock();
        $router
            ->expects($this->never())
            ->method('generate');

        $cacheManager = new CacheManager($config, $router, new Signer('secret'), $this->createEventDispatcherInterfaceMock());
        $cacheManager->addResolver('default', $resolver);

        $actualBrowserPath = $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail');

        $this->assertEquals('http://a/path/to/an/image.png', $actualBrowserPath);
    }

    public function testFilterActionUrlGeneratedAndReturnIfResolverReturnNullOnGetBrowserPath()
    {
        $resolver = $this->createCacheResolverInterfaceMock();
        $resolver
            ->expects($this->once())
            ->method('isStored')
            ->with('cats.jpeg', 'thumbnail')
            ->will($this->returnValue(false));
        $resolver
            ->expects($this->never())
            ->method('resolve');

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'cache' => null,
            )));

        $router = $this->createRouterInterfaceMock();
        $router
            ->expects($this->once())
            ->method('generate')
            ->will($this->returnValue('/media/cache/thumbnail/cats.jpeg'));

        $cacheManager = new CacheManager($config, $router, new Signer('secret'), $this->createEventDispatcherInterfaceMock());
        $cacheManager->addResolver('default', $resolver);

        $actualBrowserPath = $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail');

        $this->assertEquals('/media/cache/thumbnail/cats.jpeg', $actualBrowserPath);
    }

    public function testFilterActionUrlGeneratedAndReturnIfResolverReturnNullOnGetBrowserPathWithRuntimeConfig()
    {
        $runtimeConfig = array(
            'thumbnail' => array(
                'size' => array(100, 100),
            ),
        );

        $resolver = $this->createCacheResolverInterfaceMock();
        $resolver
            ->expects($this->once())
            ->method('isStored')
            ->with('rc/VhOzTGRB/cats.jpeg', 'thumbnail')
            ->will($this->returnValue(false));
        $resolver
            ->expects($this->never())
            ->method('resolve');

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'cache' => null,
            )));

        $router = $this->createRouterInterfaceMock();
        $router
            ->expects($this->once())
            ->method('generate')
            ->will($this->returnValue('/media/cache/thumbnail/rc/VhOzTGRB/cats.jpeg'));

        $cacheManager = new CacheManager($config, $router, new Signer('secret'), $this->createEventDispatcherInterfaceMock());
        $cacheManager->addResolver('default', $resolver);

        $actualBrowserPath = $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail', $runtimeConfig);

        $this->assertEquals('/media/cache/thumbnail/rc/VhOzTGRB/cats.jpeg', $actualBrowserPath);
    }

    /**
     * @dataProvider invalidPathProvider
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testResolveInvalidPath($path)
    {
        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock()
        );

        $cacheManager->resolve($path, 'thumbnail');
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Could not find resolver "default" for "thumbnail" filter type
     */
    public function testThrowsIfConcreteResolverNotExists()
    {
        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock()
        );

        $this->assertFalse($cacheManager->resolve('cats.jpeg', 'thumbnail'));
    }

    public function testFallbackToDefaultResolver()
    {
        $binary = new Binary('aContent', 'image/png', 'png');

        $resolver = $this->createCacheResolverInterfaceMock();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('cats.jpeg', 'thumbnail')
            ->will($this->returnValue('/thumbs/cats.jpeg'));
        $resolver
            ->expects($this->once())
            ->method('store')
            ->with($binary, '/thumbs/cats.jpeg', 'thumbnail');
        $resolver
            ->expects($this->once())
            ->method('remove')
            ->with(array('/thumbs/cats.jpeg'), array('thumbnail'))
            ->will($this->returnValue(true));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->exactly(3))
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'cache' => null,
            )));

        $cacheManager = new CacheManager(
            $config,
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock()
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

        $routerMock = $this->createRouterInterfaceMock();
        $routerMock
            ->expects($this->once())
            ->method('generate')
            ->with(
                'liip_imagine_filter',
                array(
                    'path' => $path,
                    'filter' => 'thumbnail',
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->will($this->returnValue($expectedUrl));

        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $routerMock,
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock()
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

        $resolver = $this->createCacheResolverInterfaceMock();
        $resolver
            ->expects($this->once())
            ->method('remove')
            ->with(array($expectedPath), array($expectedFilter));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function ($filter) {
                return array(
                    'cache' => $filter,
                );
            }));

        $cacheManager = new CacheManager($config, $this->createRouterInterfaceMock(), new Signer('secret'), $this->createEventDispatcherInterfaceMock());
        $cacheManager->addResolver($expectedFilter, $resolver);
        $cacheManager->remove($expectedPath, $expectedFilter);
    }

    public function testRemoveCacheForPathAndSomeFiltersOnRemove()
    {
        $expectedPath = 'thePath';
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $resolverOne = $this->createCacheResolverInterfaceMock();
        $resolverOne
            ->expects($this->once())
            ->method('remove')
            ->with(array($expectedPath), array($expectedFilterOne));

        $resolverTwo = $this->createCacheResolverInterfaceMock();
        $resolverTwo
            ->expects($this->once())
            ->method('remove')
            ->with(array($expectedPath), array($expectedFilterTwo));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function ($filter) {
                return array(
                    'cache' => $filter,
                );
            }));

        $cacheManager = new CacheManager($config, $this->createRouterInterfaceMock(), new Signer('secret'), $this->createEventDispatcherInterfaceMock());
        $cacheManager->addResolver($expectedFilterOne, $resolverOne);
        $cacheManager->addResolver($expectedFilterTwo, $resolverTwo);
        $cacheManager->remove($expectedPath, array($expectedFilterOne, $expectedFilterTwo));
    }

    public function testRemoveCacheForSomePathsAndFilterOnRemove()
    {
        $expectedPathOne = 'thePathOne';
        $expectedPathTwo = 'thePathTwo';
        $expectedFilter = 'theFilter';

        $resolver = $this->createCacheResolverInterfaceMock();
        $resolver
            ->expects($this->once())
            ->method('remove')
            ->with(
                array($expectedPathOne, $expectedPathTwo),
                array($expectedFilter)
            );

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function ($filter) {
                return array(
                    'cache' => $filter,
                );
            }));

        $cacheManager = new CacheManager($config, $this->createRouterInterfaceMock(), new Signer('secret'), $this->createEventDispatcherInterfaceMock());
        $cacheManager->addResolver($expectedFilter, $resolver);
        $cacheManager->remove(array($expectedPathOne, $expectedPathTwo), $expectedFilter);
    }

    public function testRemoveCacheForSomePathsAndSomeFiltersOnRemove()
    {
        $expectedPathOne = 'thePath';
        $expectedPathTwo = 'thePath';
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $resolverOne = $this->createCacheResolverInterfaceMock();
        $resolverOne
            ->expects($this->once())
            ->method('remove')
            ->with(array($expectedPathOne, $expectedPathTwo), array($expectedFilterOne));

        $resolverTwo = $this->createCacheResolverInterfaceMock();
        $resolverTwo
            ->expects($this->once())
            ->method('remove')
            ->with(array($expectedPathOne, $expectedPathTwo), array($expectedFilterTwo));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function ($filter) {
                return array(
                    'cache' => $filter,
                );
            }));

        $cacheManager = new CacheManager($config, $this->createRouterInterfaceMock(), new Signer('secret'), $this->createEventDispatcherInterfaceMock());
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

        $resolverOne = $this->createCacheResolverInterfaceMock();
        $resolverOne
            ->expects($this->once())
            ->method('remove')
            ->with(array(), array($expectedFilterOne));

        $resolverTwo = $this->createCacheResolverInterfaceMock();
        $resolverTwo
            ->expects($this->once())
            ->method('remove')
            ->with(array(), array($expectedFilterTwo));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function ($filter) {
                return array(
                    'cache' => $filter,
                );
            }));
        $config
            ->expects($this->once())
            ->method('all')
            ->will($this->returnValue(array(
                $expectedFilterOne => array(),
                $expectedFilterTwo => array(),
            )));

        $cacheManager = new CacheManager($config, $this->createRouterInterfaceMock(), new Signer('secret'), $this->createEventDispatcherInterfaceMock());
        $cacheManager->addResolver($expectedFilterOne, $resolverOne);
        $cacheManager->addResolver($expectedFilterTwo, $resolverTwo);
        $cacheManager->remove();
    }

    public function testRemoveCacheForPathAndAllFiltersOnRemove()
    {
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';
        $expectedPath = 'thePath';

        $resolverOne = $this->createCacheResolverInterfaceMock();
        $resolverOne
            ->expects($this->once())
            ->method('remove')
            ->with(array($expectedPath), array($expectedFilterOne));

        $resolverTwo = $this->createCacheResolverInterfaceMock();
        $resolverTwo
            ->expects($this->once())
            ->method('remove')
            ->with(array($expectedPath), array($expectedFilterTwo));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function ($filter) {
                return array(
                    'cache' => $filter,
                );
            }));
        $config
            ->expects($this->once())
            ->method('all')
            ->will($this->returnValue(array(
                $expectedFilterOne => array(),
                $expectedFilterTwo => array(),
            )));

        $cacheManager = new CacheManager($config, $this->createRouterInterfaceMock(), new Signer('secret'), $this->createEventDispatcherInterfaceMock());
        $cacheManager->addResolver($expectedFilterOne, $resolverOne);
        $cacheManager->addResolver($expectedFilterTwo, $resolverTwo);
        $cacheManager->remove($expectedPath);
    }

    public function testAggregateFiltersByResolverOnRemove()
    {
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $resolver = $this->createCacheResolverInterfaceMock();
        $resolver
            ->expects($this->once())
            ->method('remove')
            ->with(array(), array($expectedFilterOne, $expectedFilterTwo));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function ($filter) {
                return array(
                    'cache' => $filter,
                );
            }));

        $cacheManager = new CacheManager($config, $this->createRouterInterfaceMock(), new Signer('secret'), $this->createEventDispatcherInterfaceMock());
        $cacheManager->addResolver($expectedFilterOne, $resolver);
        $cacheManager->addResolver($expectedFilterTwo, $resolver);
        $cacheManager->remove(null, array($expectedFilterOne, $expectedFilterTwo));
    }

    public function testShouldDispatchCachePreResolveEvent()
    {
        $dispatcher = $this->createEventDispatcherInterfaceMock();
        $dispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(ImagineEvents::PRE_RESOLVE, new CacheResolveEvent('cats.jpg', 'thumbnail'));

        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $dispatcher
        );

        $cacheManager->addResolver('default', $this->createCacheResolverInterfaceMock());
        $cacheManager->resolve('cats.jpg', 'thumbnail');
    }

    public function testShouldDispatchCachePostResolveEvent()
    {
        $dispatcher = $this->createEventDispatcherInterfaceMock();
        $dispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(ImagineEvents::POST_RESOLVE, new CacheResolveEvent('cats.jpg', 'thumbnail'));

        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $dispatcher
        );

        $cacheManager->addResolver('default', $this->createCacheResolverInterfaceMock());
        $cacheManager->resolve('cats.jpg', 'thumbnail');
    }

    public function testShouldAllowToPassChangedDataFromPreResolveEventToResolver()
    {
        $dispatcher = $this->createEventDispatcherInterfaceMock();
        $dispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(ImagineEvents::PRE_RESOLVE, $this->isInstanceOf('\Liip\ImagineBundle\Events\CacheResolveEvent'))
            ->will($this->returnCallback(function ($name, $event) {
                $event->setPath('changed_path');
                $event->setFilter('changed_filter');
            }));

        $resolver = $this->createCacheResolverInterfaceMock();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('changed_path', 'changed_filter');

        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $dispatcher
        );

        $cacheManager->addResolver('default', $resolver);
        $cacheManager->resolve('cats.jpg', 'thumbnail');
    }

    public function testShouldAllowToGetResolverByFilterChangedInPreResolveEvent()
    {
        $dispatcher = $this->createEventDispatcherInterfaceMock();
        $dispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->will($this->returnCallback(function ($name, $event) {
                $event->setFilter('thumbnail');
            }));

        $cacheManager = $this
            ->getMockBuilder('\Liip\ImagineBundle\Imagine\Cache\CacheManager')
            ->setMethods(array('getResolver'))
            ->setConstructorArgs(array(
                $this->createFilterConfigurationMock(),
                $this->createRouterInterfaceMock(),
                new Signer('secret'),
                $dispatcher,
            ))->getMock();

        $cacheManager
            ->expects($this->once())
            ->method('getResolver')
            ->with('thumbnail')
            ->will($this->returnValue($this->createCacheResolverInterfaceMock()));

        $cacheManager->resolve('cats.jpg', 'default');
    }

    public function testShouldAllowToPassChangedDataFromPreResolveEventToPostResolveEvent()
    {
        $dispatcher = $this->createEventDispatcherInterfaceMock();
        $dispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(ImagineEvents::PRE_RESOLVE, $this->isInstanceOf('\Liip\ImagineBundle\Events\CacheResolveEvent'))
            ->will($this->returnCallback(function ($name, $event) {
                $event->setPath('changed_path');
                $event->setFilter('changed_filter');
            }))
        ;
        $dispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(
                ImagineEvents::POST_RESOLVE,
                $this->logicalAnd(
                    $this->isInstanceOf('\Liip\ImagineBundle\Events\CacheResolveEvent'),
                    $this->attributeEqualTo('path', 'changed_path'),
                    $this->attributeEqualTo('filter', 'changed_filter')
            ));

        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $dispatcher
        );

        $cacheManager->addResolver('default', $this->createCacheResolverInterfaceMock());
        $cacheManager->resolve('cats.jpg', 'thumbnail');
    }

    public function testShouldReturnUrlChangedInPostResolveEvent()
    {
        $dispatcher = $this->createEventDispatcherInterfaceMock();
        $dispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(ImagineEvents::POST_RESOLVE, $this->isInstanceOf('\Liip\ImagineBundle\Events\CacheResolveEvent'))
            ->will($this->returnCallback(function ($name, $event) {
                $event->setUrl('changed_url');
            }));

        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $dispatcher
        );
        $cacheManager->addResolver('default', $this->createCacheResolverInterfaceMock());

        $this->assertEquals('changed_url', $cacheManager->resolve('cats.jpg', 'thumbnail'));
    }
}
