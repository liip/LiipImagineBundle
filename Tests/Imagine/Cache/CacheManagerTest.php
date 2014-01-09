<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\CacheManager
 */
class CacheManagerTest extends AbstractTest
{
    protected $resolver;

    public function testGetWebRoot()
    {
        $cacheManager = new CacheManager($this->getMockFilterConfiguration(), $this->getMockRouter(), $this->fixturesDir.'/assets');
        $this->assertEquals(str_replace('/', DIRECTORY_SEPARATOR, $this->fixturesDir.'/assets'), $cacheManager->getWebRoot());
    }

    public function testAddCacheManagerAwareResolver()
    {
        $cacheManager = new CacheManager($this->getMockFilterConfiguration(), $this->getMockRouter(), $this->fixturesDir.'/assets');

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
        $config = $this->getMockFilterConfiguration();
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

        $cacheManager = new CacheManager($config, $this->getMockRouter(), $this->fixturesDir.'/assets', 'default');

        $this->setExpectedException('InvalidArgumentException', 'Could not find resolver for "thumbnail" filter type');
        $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail', true);
    }

    public function testDefaultResolverUsedIfNoneSetOnGetBrowserPath()
    {
        $resolver = $this->getMockResolver();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('cats.jpeg', 'thumbnail')
            ->will($this->returnValue('http://a/path/to/an/image.png'))
        ;

        $config = $this->getMockFilterConfiguration();
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

        $router = $this->getMockRouter();
        $router
            ->expects($this->never())
            ->method('generate')
        ;

        $cacheManager = new CacheManager($config, $router, $this->fixturesDir.'/assets', 'default');
        $cacheManager->addResolver('default', $resolver);

        $actualBrowserPath = $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail', true);

        $this->assertEquals('http://a/path/to/an/image.png', $actualBrowserPath);
    }

    public function testFilterActionUrlGeneratedAndReturnIfResolverReturnNullOnGetBrowserPath()
    {
        $resolver = $this->getMockResolver();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('cats.jpeg', 'thumbnail')
            ->will($this->returnValue(null))
        ;

        $config = $this->getMockFilterConfiguration();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'format' => 'png',
                'size' => array(180, 180),
                'mode' => 'outbound',
                'cache' => null,
            )))
        ;

        $router = $this->getMockRouter();
        $router
            ->expects($this->once())
            ->method('generate')
            ->will($this->returnValue('/media/cache/thumbnail/cats.jpeg'))
        ;

        $cacheManager = new CacheManager($config, $router, $this->fixturesDir.'/assets', 'default');
        $cacheManager->addResolver('default', $resolver);

        $actualBrowserPath = $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail', true);

        $this->assertEquals('/media/cache/thumbnail/cats.jpeg', $actualBrowserPath);
    }

    /**
     * @dataProvider invalidPathProvider
     */
    public function testResolveInvalidPath($path)
    {
        $cacheManager = new CacheManager($this->getMockFilterConfiguration(), $this->getMockRouter(), $this->fixturesDir.'/assets');

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $cacheManager->resolve($path, 'thumbnail');
    }

    public function testResolveWithoutResolver()
    {
        $cacheManager = new CacheManager($this->getMockFilterConfiguration(), $this->getMockRouter(), $this->fixturesDir.'/assets');

        $this->assertFalse($cacheManager->resolve('cats.jpeg', 'thumbnail'));
    }

    public function testFallbackToDefaultResolver()
    {
        $response = new Response('', 200);

        $resolver = $this->getMockResolver();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('cats.jpeg', 'thumbnail')
            ->will($this->returnValue('/thumbs/cats.jpeg'))
        ;
        $resolver
            ->expects($this->once())
            ->method('store')
            ->with($response, '/thumbs/cats.jpeg', 'thumbnail')
            ->will($this->returnValue($response))
        ;
        $resolver
            ->expects($this->once())
            ->method('remove')
            ->with('/thumbs/cats.jpeg', 'thumbnail')
            ->will($this->returnValue(true))
        ;

        $config = $this->getMockFilterConfiguration();
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

        $cacheManager = new CacheManager($config, $this->getMockRouter(), $this->fixturesDir.'/assets', 'default');
        $cacheManager->addResolver('default', $resolver);

        // Resolve fallback to default resolver
        $this->assertEquals('/thumbs/cats.jpeg', $cacheManager->resolve('cats.jpeg', 'thumbnail'));

        // Store fallback to default resolver
        $this->assertEquals($response, $cacheManager->store($response, '/thumbs/cats.jpeg', 'thumbnail'));

        // Remove fallback to default resolver
        $this->assertTrue($cacheManager->remove('/thumbs/cats.jpeg', 'thumbnail'));
    }

    public function testClearResolversCacheClearsAll()
    {
        $resolver = $this->getMockResolver();
        $resolver
            ->expects($this->exactly(5))
            ->method('clear')
            ->with('imagine_cache')
        ;

        $cacheManager = new CacheManager($this->getMockFilterConfiguration(), $this->getMockRouter(), $this->fixturesDir.'/assets', 'default');

        $cacheManager->addResolver('default', $resolver);
        $cacheManager->addResolver('thumbnail1', $resolver);
        $cacheManager->addResolver('thumbnail2', $resolver);
        $cacheManager->addResolver('thumbnail3', $resolver);
        $cacheManager->addResolver('thumbnail4', $resolver);

        $cacheManager->clearResolversCache('imagine_cache');
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
        $config = new FilterConfiguration;
        $config->set('thumbnail', $filterConfig);

        $router = $this->getMockRouter();
        $router
            ->expects($this->once())
            ->method('generate')
            ->with('_imagine_thumbnail', array(
                'path' => $expectedPath,
            ), true)
        ;

        $cacheManager = new CacheManager($config, $router, $this->fixturesDir.'/assets');
        $cacheManager->generateUrl($path, 'thumbnail', true);
    }
}
