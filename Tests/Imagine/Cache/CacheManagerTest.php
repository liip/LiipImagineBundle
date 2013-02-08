<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\CacheManager
 */
class CacheManagerTest extends AbstractTest
{
    protected $resolver;

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

    public function testDefaultResolverUsedIfNoneSet()
    {
        $resolver = $this->getMockResolver();
        $resolver
            ->expects($this->once())
            ->method('getBrowserPath')
            ->with('cats.jpeg', 'thumbnail', true)
        ;

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
        $cacheManager->addResolver('default', $resolver);

        $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail', true);
    }

    public function invalidPathProvider()
    {
        return array(
            array($this->fixturesDir.'/assets/../../foobar.png'),
            array($this->fixturesDir.'/assets/some_folder/../foobar.png'),
            array('../../outside/foobar.jpg'),
        );
    }

    /**
     * @dataProvider invalidPathProvider
     */
    public function testResolveInvalidPath($path)
    {
        $cacheManager = new CacheManager($this->getMockFilterConfiguration(), $this->getMockRouter(), $this->fixturesDir.'/assets');

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $cacheManager->resolve(new Request(), $path, 'thumbnail');
    }

    public function testResolveWithoutResolver()
    {
        $cacheManager = new CacheManager($this->getMockFilterConfiguration(), $this->getMockRouter(), $this->fixturesDir.'/assets');

        $this->assertFalse($cacheManager->resolve(new Request(), 'cats.jpeg', 'thumbnail'));
    }

    public function testFallbackToDefaultResolver()
    {
        $response = new Response('', 200);
        $request = new Request();

        $resolver = $this->getMockResolver();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($request, 'cats.jpeg', 'thumbnail')
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
        $this->assertEquals('/thumbs/cats.jpeg', $cacheManager->resolve($request, 'cats.jpeg', 'thumbnail'));

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
}
