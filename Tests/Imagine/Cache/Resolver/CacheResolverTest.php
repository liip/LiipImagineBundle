<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\Resolver\CacheResolver;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;
use Liip\ImagineBundle\Tests\Fixtures\MemoryCache;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\CacheResolver
 */
class CacheResolverTest extends AbstractTest
{
    protected $filter = 'thumbnail';
    protected $path = 'MadCat2.jpeg';
    protected $webPath = '/media/cache/thumbnail/MadCat2.jpeg';

    public function testResolveIsSavedToCache()
    {
        $resolver = $this->getMockResolver();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->path, $this->filter)
            ->will($this->returnValue($this->webPath))
        ;

        $cacheResolver = new CacheResolver(new MemoryCache(), $resolver);

        $this->assertEquals($this->webPath, $cacheResolver->resolve($this->path, $this->filter));

        // Call multiple times to verify the cache is used.
        $this->assertEquals($this->webPath, $cacheResolver->resolve($this->path, $this->filter));
        $this->assertEquals($this->webPath, $cacheResolver->resolve($this->path, $this->filter));
    }

    public function testStoreIsForwardedToResolver()
    {
        $binary = new Binary('aContent', 'image/jpeg', 'jpg');

        $resolver = $this->getMockResolver();
        $resolver
            ->expects($this->exactly(2))
            ->method('store')
            ->with($this->identicalTo($binary), $this->webPath, $this->filter)
        ;

        $cacheResolver = new CacheResolver(new MemoryCache(), $resolver);

        // Call twice, as this method should not be cached.
        $this->assertNull($cacheResolver->store($binary, $this->webPath, $this->filter));
        $this->assertNull($cacheResolver->store($binary, $this->webPath, $this->filter));
    }

    public function testSavesToCacheIfInternalResolverReturnUrlOnResolve()
    {
        $resolver = $this->getMockResolver();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->path, $this->filter)
            ->will($this->returnValue('/the/expected/browser'))
        ;

        $cache = $this->getMock('Doctrine\Common\Cache\Cache');
        $cache
            ->expects($this->exactly(1))
            ->method('save')
        ;

        $cacheResolver = new CacheResolver($cache, $resolver);

        $cacheResolver->resolve($this->path, $this->filter);
    }

    public function testNotSavesToCacheIfInternalResolverReturnNullOnResolve()
    {
        $resolver = $this->getMockResolver();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->path, $this->filter)
            ->will($this->returnValue(null))
        ;

        $cache = $this->getMock('Doctrine\Common\Cache\Cache');
        $cache
            ->expects($this->never())
            ->method('save')
        ;

        $cacheResolver = new CacheResolver($cache, $resolver);

        $cacheResolver->resolve($this->path, $this->filter);
    }

    /**
     * @depends testResolveIsSavedToCache
     */
    public function testRemoveUsesIndex()
    {
        $resolver = $this->getMockResolver();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->path, $this->filter)
            ->will($this->returnValue($this->webPath))
        ;
        $resolver
            ->expects($this->once())
            ->method('remove')
        ;

        $cache = new MemoryCache();

        $cacheResolver = new CacheResolver($cache, $resolver);
        $cacheResolver->resolve($this->path, $this->filter);

        /*
         * Three items:
         * * The result of resolve.
         * * The result of reverse for the filePath.
         * * The index of both entries.
         */
        $this->assertCount(2, $cache->data);

        $cacheResolver->remove($this->filter, $this->path);

        // Cache including index has been removed.
        $this->assertCount(0, $cache->data);
    }
}
