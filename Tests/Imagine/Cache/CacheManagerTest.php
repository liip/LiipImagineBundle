<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\CacheManager
 */
class CacheManagerTest extends AbstractTest
{
    protected $resolver;

    public function testAddCacheManagerAwareResolver()
    {
        $cacheManager = new CacheManager($this->createFilterConfigurationMock(), $this->createRouterMock());

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

        $cacheManager = new CacheManager($config, $this->createRouterMock(), 'default');

        $this->setExpectedException('OutOfBoundsException', 'Could not find resolver for "thumbnail" filter type');
        $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail', true);
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

        $cacheManager = new CacheManager($config, $router, 'default');
        $cacheManager->addResolver('default', $resolver);

        $actualBrowserPath = $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail', true);

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

        $cacheManager = new CacheManager($config, $router, 'default');
        $cacheManager->addResolver('default', $resolver);

        $actualBrowserPath = $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail', true);

        $this->assertEquals('/media/cache/thumbnail/cats.jpeg', $actualBrowserPath);
    }

    /**
     * @dataProvider invalidPathProvider
     */
    public function testResolveInvalidPath($path)
    {
        $cacheManager = new CacheManager($this->createFilterConfigurationMock(), $this->createRouterMock());

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $cacheManager->resolve($path, 'thumbnail');
    }

    public function testThrowsIfConcreteResolverNotExists()
    {
        $cacheManager = new CacheManager($this->createFilterConfigurationMock(), $this->createRouterMock());

        $this->setExpectedException('OutOfBoundsException', 'Could not find resolver for "thumbnail" filter type');
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

        $cacheManager = new CacheManager($config, $this->createRouterMock(), 'default');
        $cacheManager->addResolver('default', $resolver);

        // Resolve fallback to default resolver
        $this->assertEquals('/thumbs/cats.jpeg', $cacheManager->resolve('cats.jpeg', 'thumbnail'));

        $cacheManager->store($binary, '/thumbs/cats.jpeg', 'thumbnail');

        // Remove fallback to default resolver
        $cacheManager->remove('/thumbs/cats.jpeg', 'thumbnail');
    }

    public function generateUrlProvider()
    {
        return array(
            // Simple route generation
            array(array(), '/thumbnail/cats.jpeg', 'thumbnail/cats.jpeg'),

            // 'format' given, altering URL
            array(array(
                'format' => 'jpg',
            ), '/thumbnail/cats.jpeg', 'thumbnail/cats.jpg'),

            // No 'extension' path info
            array(array(
                'format' => 'jpg',
            ), '/thumbnail/cats', 'thumbnail/cats.jpg'),

            // No 'extension' path info, and no directory
            array(array(
                'format' => 'jpg',
            ), '/cats', 'cats.jpg'),
        );
    }

    /**
     * @dataProvider generateUrlProvider
     */
    public function testGenerateUrl($filterConfig, $path, $expectedPath)
    {
        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue($filterConfig))
        ;

        $router = $this->createRouterMock();
        $router
            ->expects($this->once())
            ->method('generate')
            ->with('_imagine_thumbnail', array(
                'path' => $expectedPath,
            ), true)
        ;

        $cacheManager = new CacheManager($config, $router);
        $cacheManager->generateUrl($path, 'thumbnail', true);
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

        $cacheManager = new CacheManager($config, $this->createRouterMock());
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

        $cacheManager = new CacheManager($config, $this->createRouterMock());
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

        $cacheManager = new CacheManager($config, $this->createRouterMock());
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

        $cacheManager = new CacheManager($config, $this->createRouterMock());
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

        $cacheManager = new CacheManager($config, $this->createRouterMock());
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

        $cacheManager = new CacheManager($config, $this->createRouterMock());
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

        $cacheManager = new CacheManager($config, $this->createRouterMock());
        $cacheManager->addResolver($expectedFilterOne, $resolver);
        $cacheManager->addResolver($expectedFilterTwo, $resolver);

        $cacheManager->remove(null, array($expectedFilterOne, $expectedFilterTwo));
    }
}
