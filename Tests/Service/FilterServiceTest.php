<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Service;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Exception\Imagine\Filter\NonExistingFilterException;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Service\FilterService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Liip\ImagineBundle\Service\FilterService
 */
final class FilterServiceTest extends TestCase
{
    private const SOURCE_IMAGE = '/images/cats.jpeg';
    private const RUNTIME_IMAGE = '/filter_hash/images/cats.jpeg';
    private const FILTER = 'thumbnail_web_path';
    private const RUNTIME_FILTERS = [
        'thumbnail' => [
            'size' => [50, 50],
        ],
    ];
    private const WEBP_OPTIONS = [
        'quality' => 100,
        'post_processors' => [
            'my_custom_webp_post_processor' => [],
        ],
    ];

    /**
     * @var MockObject|DataManager
     */
    private $dataManager;

    /**
     * @var MockObject|FilterManager
     */
    private $filterManager;

    /**
     * @var MockObject|CacheManager
     */
    private $cacheManager;

    /**
     * @var MockObject|LoggerInterface
     */
    private $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dataManager = $this
            ->getMockBuilder(DataManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterManager = $this
            ->getMockBuilder(FilterManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheManager = $this
            ->getMockBuilder(CacheManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this
            ->getMockBuilder(LoggerInterface::class)
            ->getMock();
    }

    public function provideWebpGeneration(): \Traversable
    {
        yield 'WebP generation enabled' => [true];

        yield 'WebP generation disabled' => [false];
    }

    public function testBustCacheWebp(): void
    {
        $service = $this->createFilterService(true);

        $this->cacheManager
            ->expects($this->exactly(2))
            ->method('isStored')
            ->withConsecutive(
                [self::SOURCE_IMAGE],
                [self::SOURCE_IMAGE.'.webp']
            )
            ->willReturn(true);
        $this->cacheManager
            ->expects($this->exactly(2))
            ->method('remove')
            ->withConsecutive(
                [self::SOURCE_IMAGE, self::FILTER],
                [self::SOURCE_IMAGE.'.webp', self::FILTER]
            );

        $this->assertTrue($service->bustCache(self::SOURCE_IMAGE, self::FILTER));
    }

    public function testNothingBustCache(): void
    {
        $service = $this->createFilterService(true);

        $this->cacheManager
            ->expects($this->exactly(2))
            ->method('isStored')
            ->withConsecutive(
                [self::SOURCE_IMAGE],
                [self::SOURCE_IMAGE.'.webp']
            )
            ->willReturn(false);
        $this->cacheManager
            ->expects($this->never())
            ->method('remove');

        $this->assertFalse($service->bustCache(self::SOURCE_IMAGE, self::FILTER));
    }

    /**
     * @dataProvider provideWebpGeneration
     */
    public function testWarmsUpCache(bool $webpGenerate): void
    {
        $resolver = null;

        $service = $this->createFilterService($webpGenerate);

        $this->expectCacheManagerIsStored(self::SOURCE_IMAGE, $webpGenerate, true, $resolver);
        $this->cacheManager
            ->expects($this->never())
            ->method('store');

        $this->dataManager
            ->expects($this->never())
            ->method('find');

        $this->filterManager
            ->expects($this->never())
            ->method('applyFilter');

        $this->assertFalse($service->warmUpCache(self::SOURCE_IMAGE, self::FILTER, $resolver));
    }

    /**
     * @dataProvider provideWebpGeneration
     */
    public function testWarmsUpCacheForced(bool $webpGenerate): void
    {
        $resolver = null;
        $binary = $this
            ->getMockBuilder(BinaryInterface::class)
            ->getMock();

        $service = $this->createFilterService($webpGenerate);

        $this->cacheManager
            ->expects($this->never())
            ->method('isStored');
        $this->expectCacheManagerStore(self::SOURCE_IMAGE, $webpGenerate, $resolver);
        $this->expectDataManagerFind($webpGenerate, $binary);
        $this->expectFilterManagerApplyFilter($webpGenerate, $binary);

        $this->assertTrue($service->warmUpCache(self::SOURCE_IMAGE, self::FILTER, $resolver, true));
    }

    /**
     * @dataProvider provideWebpGeneration
     */
    public function testWarmsUpCacheNotStored(bool $webpGenerate): void
    {
        $resolver = null;
        $binary = $this
            ->getMockBuilder(BinaryInterface::class)
            ->getMock();

        $service = $this->createFilterService($webpGenerate);

        $this->expectCacheManagerIsStored(self::SOURCE_IMAGE, $webpGenerate, false, $resolver);
        $this->expectCacheManagerStore(self::SOURCE_IMAGE, $webpGenerate, $resolver);
        $this->expectDataManagerFind($webpGenerate, $binary);
        $this->expectFilterManagerApplyFilter($webpGenerate, $binary);

        $this->assertTrue($service->warmUpCache(self::SOURCE_IMAGE, self::FILTER, $resolver));
    }

    /**
     * @dataProvider provideWebpGeneration
     */
    public function testWarmsUpCacheNotStoredForced(bool $webpGenerate): void
    {
        $resolver = null;
        $binary = $this
            ->getMockBuilder(BinaryInterface::class)
            ->getMock();
        $filteredBinary = $this
            ->getMockBuilder(BinaryInterface::class)
            ->getMock();

        $service = $this->createFilterService($webpGenerate);

        $this->cacheManager
            ->expects($this->never())
            ->method('isStored');
        $this->expectCacheManagerStore(self::SOURCE_IMAGE, $webpGenerate, $resolver);
        $this->expectDataManagerFind($webpGenerate, $binary);
        $this->expectFilterManagerApplyFilter($webpGenerate, $binary);

        $this->assertTrue($service->warmUpCache(self::SOURCE_IMAGE, self::FILTER, $resolver, true));
    }

    /**
     * @dataProvider provideWebpGeneration
     */
    public function testWarmsUpCacheNonExistingFilter(bool $webpGenerate): void
    {
        $this->expectException(NonExistingFilterException::class);

        $resolver = null;
        $binary = $this
            ->getMockBuilder(BinaryInterface::class)
            ->getMock();
        $exception = new NonExistingFilterException('Filter not found');

        $service = $this->createFilterService($webpGenerate);

        // we stop after the exception, only one call expected even with webp
        $this->dataManager
            ->expects($this->once())
            ->method('find')
            ->with(self::FILTER, self::SOURCE_IMAGE)
            ->willReturn($binary);

        $this->filterManager
            ->expects($this->once())
            ->method('applyFilter')
            ->with($binary, self::FILTER, [])
            ->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with(sprintf(
                'Could not locate filter "%s" for path "%s". Message was "%s"',
                self::FILTER,
                self::SOURCE_IMAGE,
                $exception->getMessage()
            ));

        $service->warmUpCache(self::SOURCE_IMAGE, self::FILTER, $resolver, true);
    }

    /**
     * @dataProvider provideWebpGeneration
     */
    public function testGetUrlOfFilteredImage(bool $webpGenerate): void
    {
        $resolver = null;
        $url = 'https://example.com/cache'.self::SOURCE_IMAGE;

        $service = $this->createFilterService($webpGenerate);

        $this->cacheManager
            ->expects($this->once())
            ->method('resolve')
            ->with(self::SOURCE_IMAGE, self::FILTER, $resolver)
            ->willReturn($url);
        $this->expectCacheManagerIsStored(self::SOURCE_IMAGE, $webpGenerate, true, $resolver);
        $this->cacheManager
            ->expects($this->never())
            ->method('store');
        $this->dataManager
            ->expects($this->never())
            ->method('find');
        $this->filterManager
            ->expects($this->never())
            ->method('applyFilter');

        $this->assertSame($url, $service->getUrlOfFilteredImage(self::SOURCE_IMAGE, self::FILTER, $resolver));
    }

    /**
     * @dataProvider provideWebpGeneration
     */
    public function testGetUrlOfFilteredImageNotStored(bool $webpGenerate): void
    {
        $resolver = null;
        $url = 'https://example.com/cache'.self::SOURCE_IMAGE;
        $binary = $this
            ->getMockBuilder(BinaryInterface::class)
            ->getMock();

        $service = $this->createFilterService($webpGenerate);

        $this->cacheManager
            ->expects($this->once())
            ->method('resolve')
            ->with(self::SOURCE_IMAGE, self::FILTER, $resolver)
            ->willReturn($url);

        $this->expectCacheManagerIsStored(self::SOURCE_IMAGE, $webpGenerate, false, $resolver);
        $this->expectCacheManagerStore(self::SOURCE_IMAGE, $webpGenerate, $resolver);
        $this->expectDataManagerFind($webpGenerate, $binary);
        $this->expectFilterManagerApplyFilter($webpGenerate, $binary);

        $this->assertSame($url, $service->getUrlOfFilteredImage(self::SOURCE_IMAGE, self::FILTER, $resolver));
    }

    /**
     * @dataProvider provideWebpGeneration
     */
    public function testGetUrlOfFilteredImageNotExistingFilter(bool $webpGenerate): void
    {
        $this->expectException(NonExistingFilterException::class);

        $resolver = null;
        $binary = $this
            ->getMockBuilder(BinaryInterface::class)
            ->getMock();
        $exception = new NonExistingFilterException('Filter not found');

        $service = $this->createFilterService($webpGenerate);

        $this->cacheManager
            ->expects($this->once())
            ->method('isStored')
            ->with(self::SOURCE_IMAGE, self::FILTER, $resolver)
            ->willReturn(false);

        $this->dataManager
            ->expects($this->once())
            ->method('find')
            ->with(self::FILTER, self::SOURCE_IMAGE)
            ->willReturn($binary);

        $this->filterManager
            ->expects($this->once())
            ->method('applyFilter')
            ->with($binary, self::FILTER, [])
            ->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with(sprintf(
                'Could not locate filter "%s" for path "%s". Message was "%s"',
                self::FILTER,
                self::SOURCE_IMAGE,
                $exception->getMessage()
            ));

        $service->getUrlOfFilteredImage(self::SOURCE_IMAGE, self::FILTER, $resolver);
    }

    /**
     * @dataProvider provideWebpGeneration
     */
    public function testGetUrlOfFilteredWithRuntimeFiltersImage(bool $webpGenerate): void
    {
        $resolver = null;
        $url = 'https://example.com/cache'.self::RUNTIME_IMAGE;

        $service = $this->createFilterService($webpGenerate);

        $this->expectCacheManagerIsStored(self::RUNTIME_IMAGE, $webpGenerate, true, $resolver);
        $this->cacheManager
            ->expects($this->once())
            ->method('resolve')
            ->with(self::RUNTIME_IMAGE, self::FILTER, $resolver)
            ->willReturn($url);
        $this->cacheManager
            ->expects($this->once())
            ->method('getRuntimePath')
            ->with(self::SOURCE_IMAGE, self::RUNTIME_FILTERS)
            ->willReturn(self::RUNTIME_IMAGE);

        $this->cacheManager
            ->expects($this->never())
            ->method('store');
        $this->dataManager
            ->expects($this->never())
            ->method('find');
        $this->filterManager
            ->expects($this->never())
            ->method('applyFilter');

        $result = $service->getUrlOfFilteredImageWithRuntimeFilters(
            self::SOURCE_IMAGE,
            self::FILTER,
            self::RUNTIME_FILTERS,
            $resolver
        );

        $this->assertSame($url, $result);
    }

    /**
     * @dataProvider provideWebpGeneration
     */
    public function testGetUrlOfFilteredImageWithRuntimeFiltersNotStored(bool $webpGenerate): void
    {
        $resolver = null;
        $url = 'https://example.com/cache'.self::RUNTIME_IMAGE;
        $runtimeOptions = [
            'filters' => self::RUNTIME_FILTERS,
        ];
        $binary = $this
            ->getMockBuilder(BinaryInterface::class)
            ->getMock();

        $service = $this->createFilterService($webpGenerate);

        $this->expectCacheManagerIsStored(self::RUNTIME_IMAGE, $webpGenerate, false, $resolver);
        $this->expectCacheManagerStore(self::RUNTIME_IMAGE, $webpGenerate, $resolver);
        $this->expectDataManagerFind($webpGenerate, $binary);
        $this->expectFilterManagerApplyFilter($webpGenerate, $binary, $runtimeOptions);
        $this->cacheManager
            ->expects($this->once())
            ->method('resolve')
            ->with(self::RUNTIME_IMAGE, self::FILTER, $resolver)
            ->willReturn($url);
        $this->cacheManager
            ->expects($this->once())
            ->method('getRuntimePath')
            ->with(self::SOURCE_IMAGE, self::RUNTIME_FILTERS)
            ->willReturn(self::RUNTIME_IMAGE);

        $result = $service->getUrlOfFilteredImageWithRuntimeFilters(
            self::SOURCE_IMAGE,
            self::FILTER,
            self::RUNTIME_FILTERS,
            $resolver
        );

        $this->assertSame($url, $result);
    }

    /**
     * @dataProvider provideWebpGeneration
     */
    public function testGetUrlOfFilteredImageWithRuntimeFiltersNotExistingFilter(bool $webpGenerate): void
    {
        $this->expectException(NonExistingFilterException::class);
        $resolver = null;
        $runtimeOptions = [
            'filters' => self::RUNTIME_FILTERS,
        ];
        $binary = $this
            ->getMockBuilder(BinaryInterface::class)
            ->getMock();
        $exception = new NonExistingFilterException('Filter not found');

        $service = $this->createFilterService($webpGenerate);

        $this->cacheManager
            ->expects($this->once())
            ->method('isStored')
            ->with(self::RUNTIME_IMAGE, self::FILTER, $resolver)
            ->willReturn(false);
        $this->cacheManager
            ->expects($this->once())
            ->method('getRuntimePath')
            ->with(self::SOURCE_IMAGE, self::RUNTIME_FILTERS)
            ->willReturn(self::RUNTIME_IMAGE);

        $this->dataManager
            ->expects($this->once())
            ->method('find')
            ->with(self::FILTER, self::SOURCE_IMAGE)
            ->willReturn($binary);

        $this->filterManager
            ->expects($this->once())
            ->method('applyFilter')
            ->with($binary, self::FILTER, $runtimeOptions)
            ->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with(sprintf(
                'Could not locate filter "%s" for path "%s". Message was "%s"',
                self::FILTER,
                self::SOURCE_IMAGE,
                $exception->getMessage()
            ));

        $service->getUrlOfFilteredImageWithRuntimeFilters(
            self::SOURCE_IMAGE,
            self::FILTER,
            self::RUNTIME_FILTERS,
            $resolver
        );
    }

    private function createFilterService(bool $webpGenerate): FilterService
    {
        return new FilterService(
            $this->dataManager,
            $this->filterManager,
            $this->cacheManager,
            $webpGenerate,
            self::WEBP_OPTIONS,
            $this->logger
        );
    }

    private function expectCacheManagerIsStored(string $image, bool $webpGenerate, bool $isStored, $resolver): void
    {
        if ($webpGenerate) {
            $this->cacheManager
                ->expects($this->exactly(2))
                ->method('isStored')
                ->withConsecutive(
                    [$image, self::FILTER, $resolver],
                    [$image.'.webp', self::FILTER, $resolver]
                )
                ->willReturn($isStored);
        } else {
            $this->cacheManager
                ->expects($this->once())
                ->method('isStored')
                ->with($image, self::FILTER, $resolver)
                ->willReturn($isStored);
        }
    }

    private function expectCacheManagerStore(string $image, bool $webpGenerate, $resolver): void
    {
        $filteredBinary = $this
            ->getMockBuilder(BinaryInterface::class)
            ->getMock();

        if ($webpGenerate) {
            $this->cacheManager
                ->expects($this->exactly(2))
                ->method('store')
                ->withConsecutive(
                    [$filteredBinary, $image, self::FILTER, $resolver],
                    [$filteredBinary, $image.'.webp', self::FILTER, $resolver]
                );
        } else {
            $this->cacheManager
                ->expects($this->once())
                ->method('store')
                ->with($filteredBinary, $image, self::FILTER, $resolver);
        }
    }

    private function expectDataManagerFind(bool $webpGenerate, BinaryInterface $binary): void
    {
        $this->dataManager
            ->expects($this->exactly($webpGenerate ? 2 : 1))
            ->method('find')
            ->with(self::FILTER, self::SOURCE_IMAGE)
            ->willReturn($binary);
    }

    private function expectFilterManagerApplyFilter(bool $webpGenerate, BinaryInterface $binary, array $runtimeOptions = [])
    {
        if ($webpGenerate) {
            $this->filterManager
                ->expects($this->exactly(2))
                ->method('applyFilter')
                ->withConsecutive(
                    [$binary, self::FILTER, $runtimeOptions],
                    [$binary, self::FILTER, [
                        'format' => 'webp',
                    ] + self::WEBP_OPTIONS + $runtimeOptions]
                )
                ->willReturn($binary);
        } else {
            $this->filterManager
                ->expects($this->once())
                ->method('applyFilter')
                ->with($binary, self::FILTER, $runtimeOptions)
                ->willReturn($binary);
        }
    }
}
