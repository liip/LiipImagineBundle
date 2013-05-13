<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\Resolver\CacheResolver;

use Liip\ImagineBundle\Tests\AbstractTest;
use Liip\ImagineBundle\Tests\Fixtures\MemoryCache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\CacheResolver
 */
class CacheResolverTest extends AbstractTest
{
    protected $filter = 'thumbnail';
    protected $path = 'MadCat2.jpeg';
    protected $targetPath = '/media/cache/thumbnail/MadCat2.jpeg';

    public function testResolveIsSavedToCache()
    {
        $request = new Request();

        $resolver = $this->getMockResolver();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($request, $this->path, $this->filter)
            ->will($this->returnValue($this->targetPath))
        ;

        $cacheResolver = new CacheResolver(new MemoryCache(), $resolver);

        $this->assertEquals($this->targetPath, $cacheResolver->resolve($request, $this->path, $this->filter));

        // Call multiple times to verify the cache is used.
        $this->assertEquals($this->targetPath, $cacheResolver->resolve($request, $this->path, $this->filter));
        $this->assertEquals($this->targetPath, $cacheResolver->resolve($request, $this->path, $this->filter));
    }

    public function testStoreIsForwardedToResolver()
    {
        $response = new Response();

        $resolver = $this->getMockResolver();
        $resolver
            ->expects($this->exactly(2))
            ->method('store')
            ->with($response, $this->targetPath, $this->filter)
            ->will($this->returnValue($response))
        ;

        $cacheResolver = new CacheResolver(new MemoryCache(), $resolver);

        // Call twice, as this method should not be cached.
        $this->assertSame($response, $cacheResolver->store($response, $this->targetPath, $this->filter));
        $this->assertSame($response, $cacheResolver->store($response, $this->targetPath, $this->filter));
    }

    public function testGetBrowserPath()
    {
        $absolute = 'http://example.com' . $this->targetPath;
        $relative = $this->targetPath;

        $resolver = $this->getMockResolver();
        $resolver
            ->expects($this->at(0))
            ->method('getBrowserPath')
            ->with($this->path, $this->filter, true)
            ->will($this->returnValue($absolute))
        ;
        $resolver
            ->expects($this->at(1))
            ->method('getBrowserPath')
            ->with($this->path, $this->filter, false)
            ->will($this->returnValue($relative))
        ;

        $cacheResolver = new CacheResolver(new MemoryCache(), $resolver);

        $this->assertEquals($absolute, $cacheResolver->getBrowserPath($this->path, $this->filter, true));
        $this->assertEquals($absolute, $cacheResolver->getBrowserPath($this->path, $this->filter, true));

        $this->assertEquals($relative, $cacheResolver->getBrowserPath($this->path, $this->filter, false));
        $this->assertEquals($relative, $cacheResolver->getBrowserPath($this->path, $this->filter, false));
    }

    /**
     * @depends testResolveIsSavedToCache
     */
    public function testRemoveUsesIndex()
    {
        $request = new Request();

        $resolver = $this->getMockResolver();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($request, $this->path, $this->filter)
            ->will($this->returnValue($this->targetPath))
        ;
        $resolver
            ->expects($this->once())
            ->method('remove')
            ->will($this->returnValue(true))
        ;

        $cache = new MemoryCache();

        $cacheResolver = new CacheResolver($cache, $resolver);
        $cacheResolver->resolve($request, $this->path, $this->filter);

        /*
         * Three items:
         * * The result of resolve.
         * * The result of reverse for the targetPath.
         * * The index of both entries.
         */
        $this->assertCount(3, $cache->data);

        $this->assertTrue($cacheResolver->remove($this->targetPath, $this->filter));

        // Cache including index has been removed.
        $this->assertCount(0, $cache->data);
    }
}
