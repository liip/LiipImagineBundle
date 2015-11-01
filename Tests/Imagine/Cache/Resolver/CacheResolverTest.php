<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Doctrine\Common\Cache\ArrayCache;
use Liip\ImagineBundle\Imagine\Cache\Resolver\CacheResolver;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;

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
        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->path, $this->filter)
            ->will($this->returnValue($this->webPath))
        ;

        $cacheResolver = new CacheResolver(new ArrayCache(), $resolver);

        $this->assertEquals($this->webPath, $cacheResolver->resolve($this->path, $this->filter));

        // Call multiple times to verify the cache is used.
        $this->assertEquals($this->webPath, $cacheResolver->resolve($this->path, $this->filter));
        $this->assertEquals($this->webPath, $cacheResolver->resolve($this->path, $this->filter));
    }

    public function testNotCallInternalResolverIfCachedOnIsStored()
    {
        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->path, $this->filter)
            ->will($this->returnValue($this->webPath))
        ;
        $resolver
            ->expects($this->never())
            ->method('isStored')
        ;

        $cacheResolver = new CacheResolver(new ArrayCache(), $resolver);

        $cacheResolver->resolve($this->path, $this->filter);

        // Call multiple times to verify the cache is used.
        $this->assertTrue($cacheResolver->isStored($this->path, $this->filter));
        $this->assertTrue($cacheResolver->isStored($this->path, $this->filter));
    }

    public function testCallInternalResolverIfNotCachedOnIsStored()
    {
        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->exactly(2))
            ->method('isStored')
            ->will($this->returnValue(true))
        ;

        $cacheResolver = new CacheResolver(new ArrayCache(), $resolver);

        $this->assertTrue($cacheResolver->isStored($this->path, $this->filter));
        $this->assertTrue($cacheResolver->isStored($this->path, $this->filter));
    }

    public function testStoreIsForwardedToResolver()
    {
        $binary = new Binary('aContent', 'image/jpeg', 'jpg');

        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->exactly(2))
            ->method('store')
            ->with($this->identicalTo($binary), $this->webPath, $this->filter)
        ;

        $cacheResolver = new CacheResolver(new ArrayCache(), $resolver);

        // Call twice, as this method should not be cached.
        $this->assertNull($cacheResolver->store($binary, $this->webPath, $this->filter));
        $this->assertNull($cacheResolver->store($binary, $this->webPath, $this->filter));
    }

    public function testSavesToCacheIfInternalResolverReturnUrlOnResolve()
    {
        $resolver = $this->createResolverMock();
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

    public function testRemoveSinglePathCacheOnRemove()
    {
        $resolver = $this->createResolverMock();
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

        $cache = new ArrayCache();

        $cacheResolver = new CacheResolver($cache, $resolver);
        $cacheResolver->resolve($this->path, $this->filter);

        /*
         * Checking 2 items:
         * * The result of one resolve execution.
         * * The index of entity.
         */
        $this->assertCount(2, $this->getCacheEntries($cache));

        $cacheResolver->remove(array($this->path), array($this->filter));

        // Cache including index has been removed.
        $this->assertCount(0, $this->getCacheEntries($cache));
    }

    public function testRemoveAllFilterCacheOnRemove()
    {
        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->exactly(4))
            ->method('resolve')
            ->will($this->returnValue('aCachePath'))
        ;
        $resolver
            ->expects($this->once())
            ->method('remove')
        ;

        $cache = new ArrayCache();

        $cacheResolver = new CacheResolver($cache, $resolver);
        $cacheResolver->resolve('aPathFoo', 'thumbnail_233x233');
        $cacheResolver->resolve('aPathBar', 'thumbnail_233x233');
        $cacheResolver->resolve('aPathFoo', 'thumbnail_100x100');
        $cacheResolver->resolve('aPathBar', 'thumbnail_100x100');

        /*
         * Checking 6 items:
         * * The result of four resolve execution.
         * * The index of two entities.
         */
        $this->assertCount(6, $this->getCacheEntries($cache));

        $cacheResolver->remove(array(), array('thumbnail_233x233'));

        // Cache including index has been removed.
        $this->assertCount(3, $this->getCacheEntries($cache));
    }

    /**
     * There's an intermittent cache entry which is a cache namespace
     * version, it may or may not be there depending on doctrine-cache
     * version. There's no point in checking it anyway since it's a detail
     * of doctrine cache implementation.
     *
     * @param ArrayCache $cache
     *
     * @return array
     */
    private function getCacheEntries(ArrayCache $cache)
    {
        $cacheEntries = $this->readAttribute($cache, 'data');
        unset($cacheEntries['DoctrineNamespaceCacheKey[]']);

        return $cacheEntries;
    }
}
