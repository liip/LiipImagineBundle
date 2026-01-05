<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Binary\Locator;

use Liip\ImagineBundle\Binary\Locator\AssetMapperLocator;
use Liip\ImagineBundle\Binary\Locator\LocatorInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\AssetMapper\MappedAsset;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @covers \Liip\ImagineBundle\Binary\Locator\AssetMapperLocator
 */
class AssetMapperLocatorTest extends TestCase
{
    protected function setUp(): void
    {
        if (!interface_exists(AssetMapperInterface::class)) {
            $this->markTestSkipped('symfony/asset-mapper is required for this test.');
        }
    }

    public function testImplementsLocatorInterface(): void
    {
        $assetMapper = $this->createMock(AssetMapperInterface::class);
        $this->assertInstanceOf(LocatorInterface::class, new AssetMapperLocator($assetMapper));
    }

    public function testLocateWithoutCache(): void
    {
        $path = 'images/logo.png';
        $pathInfo = '/images/logo.png';
        $sourcePath = '/path/to/logo.png';
        $logicalPath = 'logo.png';

        $asset = new MappedAsset($logicalPath, $sourcePath, $pathInfo, $pathInfo);

        $assetMapper = $this->createMock(AssetMapperInterface::class);
        $assetMapper->expects($this->once())
            ->method('allAssets')
            ->willReturn([$asset]);

        $locator = new AssetMapperLocator($assetMapper);
        $result = $locator->locate($path);

        $this->assertSame($sourcePath, $result);
    }

    public function testLocateWithCacheHit(): void
    {
        $path = 'images/logo.png';
        $pathInfo = '/images/logo.png';
        $logicalPath = 'logo.png';
        $sourcePath = '/path/to/logo.png';

        $asset = new MappedAsset($logicalPath, $sourcePath, $pathInfo, $pathInfo);

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->expects($this->once())
            ->method('isHit')
            ->willReturn(true);
        $cacheItem->expects($this->once())
            ->method('get')
            ->willReturn($logicalPath);

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->expects($this->once())
            ->method('getItem')
            ->with(hash('xxh128', $pathInfo))
            ->willReturn($cacheItem);

        $assetMapper = $this->createMock(AssetMapperInterface::class);
        $assetMapper->expects($this->once())
            ->method('getAsset')
            ->with($logicalPath)
            ->willReturn($asset);
        $assetMapper->expects($this->never())
            ->method('allAssets');

        $locator = new AssetMapperLocator($assetMapper, $cache);
        $result = $locator->locate($path);

        $this->assertSame($sourcePath, $result);
    }

    public function testLocateWithCacheMissAndSave(): void
    {
        $path = 'images/logo.png';
        $pathInfo = '/images/logo.png';
        $logicalPath = 'logo.png';
        $sourcePath = '/path/to/logo.png';

        $asset = new MappedAsset($logicalPath, $sourcePath, $pathInfo, $pathInfo);

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->expects($this->once())
            ->method('isHit')
            ->willReturn(false);
        $cacheItem->expects($this->once())
            ->method('set')
            ->with($logicalPath);

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->expects($this->once())
            ->method('getItem')
            ->with(hash('xxh128', $pathInfo))
            ->willReturn($cacheItem);
        $cache->expects($this->once())
            ->method('save')
            ->with($cacheItem);

        $assetMapper = $this->createMock(AssetMapperInterface::class);
        $assetMapper->expects($this->once())
            ->method('allAssets')
            ->willReturn([$asset]);

        $locator = new AssetMapperLocator($assetMapper, $cache);
        $result = $locator->locate($path);

        $this->assertSame($sourcePath, $result);
    }

    public function testThrowsIfAssetNotFound(): void
    {
        $path = 'images/logo.png';

        $assetMapper = $this->createMock(AssetMapperInterface::class);
        $assetMapper->expects($this->once())
            ->method('allAssets')
            ->willReturn([]);

        $locator = new AssetMapperLocator($assetMapper);

        $this->expectException(NotLoadableException::class);
        $this->expectExceptionMessage('Asset with public path "/images/logo.png" not found.');

        $locator->locate($path);
    }
}
