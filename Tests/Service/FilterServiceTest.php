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
    private const WEBP_IMAGE = '/images/cats.jpeg.webp';
    private const RUNTIME_IMAGE = '/filter_hash/images/cats.jpeg';
    private const RUNTIME_WEBP_IMAGE = '/filter_hash/images/cats.jpeg.webp';
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

    /**
     * @dataProvider provideWebpGeneration
     */
    public function testBustCache(bool $webpGenerate): void
    {
        $service = $this->createFilterService($webpGenerate);

        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->withConsecutive(
                [self::SOURCE_IMAGE],
                [self::WEBP_IMAGE]
            )
            ->willReturn(true);
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('remove')
            ->withConsecutive(
                [self::SOURCE_IMAGE, self::FILTER],
                [self::WEBP_IMAGE, self::FILTER]
            );

        $this->assertTrue($service->bustCache(self::SOURCE_IMAGE, self::FILTER));
    }

    /**
     * @dataProvider provideWebpGeneration
     */
    public function testNothingBustCache(bool $webpGenerate): void
    {
        $service = $this->createFilterService($webpGenerate);

        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->withConsecutive(
                [self::SOURCE_IMAGE],
                [self::WEBP_IMAGE]
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

        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->withConsecutive(
                [self::SOURCE_IMAGE, self::FILTER, $resolver],
                [self::WEBP_IMAGE, self::FILTER, $resolver]
            )
            ->willReturn(true);
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
        $filteredBinary = $this
            ->getMockBuilder(BinaryInterface::class)
            ->getMock();

        $service = $this->createFilterService($webpGenerate);

        $this->cacheManager
            ->expects($this->never())
            ->method('isStored');
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('store')
            ->withConsecutive(
                [$filteredBinary, self::SOURCE_IMAGE, self::FILTER, $resolver],
                [$filteredBinary, self::WEBP_IMAGE, self::FILTER, $resolver]
            );

        $this->dataManager
            ->expects($this->atLeastOnce())
            ->method('find')
            ->with(self::FILTER, self::SOURCE_IMAGE)
            ->willReturn($binary);

        $this->filterManager
            ->expects($this->atLeastOnce())
            ->method('applyFilter')
            ->withConsecutive(
                [$binary, self::FILTER, []],
                [$binary, self::FILTER, [
                    'format' => 'webp',
                ] + self::WEBP_OPTIONS]
            )
            ->willReturn($binary);

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
        $filteredBinary = $this
            ->getMockBuilder(BinaryInterface::class)
            ->getMock();

        $service = $this->createFilterService($webpGenerate);

        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->withConsecutive(
                [self::SOURCE_IMAGE, self::FILTER, $resolver],
                [self::WEBP_IMAGE, self::FILTER, $resolver]
            )
            ->willReturn(false);
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('store')
            ->withConsecutive(
                [$filteredBinary, self::SOURCE_IMAGE, self::FILTER, $resolver],
                [$filteredBinary, self::WEBP_IMAGE, self::FILTER, $resolver]
            );

        $this->dataManager
            ->expects($this->atLeastOnce())
            ->method('find')
            ->with(self::FILTER, self::SOURCE_IMAGE)
            ->willReturn($binary);

        $this->filterManager
            ->expects($this->atLeastOnce())
            ->method('applyFilter')
            ->withConsecutive(
                [$binary, self::FILTER, []],
                [$binary, self::FILTER, [
                    'format' => 'webp',
                ] + self::WEBP_OPTIONS]
            )
            ->willReturn($binary);

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
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('store')
            ->withConsecutive(
                [$filteredBinary, self::SOURCE_IMAGE, self::FILTER, $resolver],
                [$filteredBinary, self::WEBP_IMAGE, self::FILTER, $resolver]
            );

        $this->dataManager
            ->expects($this->atLeastOnce())
            ->method('find')
            ->with(self::FILTER, self::SOURCE_IMAGE)
            ->willReturn($binary);

        $this->filterManager
            ->expects($this->atLeastOnce())
            ->method('applyFilter')
            ->withConsecutive(
                [$binary, self::FILTER, []],
                [$binary, self::FILTER, [
                    'format' => 'webp',
                ] + self::WEBP_OPTIONS]
            )
            ->willReturn($binary);

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

        $this->dataManager
            ->expects($this->atLeastOnce())
            ->method('find')
            ->with(self::FILTER, self::SOURCE_IMAGE)
            ->willReturn($binary);

        $this->filterManager
            ->expects($this->atLeastOnce())
            ->method('applyFilter')
            ->withConsecutive(
                [$binary, self::FILTER, []],
                [$binary, self::FILTER, [
                        'format' => 'webp',
                    ] + self::WEBP_OPTIONS]
            )
            ->willThrowException($exception);

        $this->logger
            ->expects($this->atLeastOnce())
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
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->withConsecutive(
                [self::SOURCE_IMAGE, self::FILTER, $resolver],
                [self::WEBP_IMAGE, self::FILTER, $resolver]
            )
            ->willReturn(true);
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('resolve')
            ->with(self::SOURCE_IMAGE, self::FILTER, $resolver)
            ->willReturn($url);
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
    public function testGetUrlOfFilteredImageWebpSupported(bool $webpGenerate): void
    {
        $resolver = null;
        $url = 'https://example.com/cache'.self::WEBP_IMAGE;

        $service = $this->createFilterService($webpGenerate);

        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->withConsecutive(
                [self::SOURCE_IMAGE, self::FILTER, $resolver],
                [self::WEBP_IMAGE, self::FILTER, $resolver]
            )
            ->willReturn(true);
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('resolve')
            ->with(self::WEBP_IMAGE, self::FILTER, $resolver)
            ->willReturn($url);

        $this->cacheManager
            ->expects($this->never())
            ->method('store');

        $this->dataManager
            ->expects($this->never())
            ->method('find');

        $this->filterManager
            ->expects($this->never())
            ->method('applyFilter');

        $this->assertSame($url, $service->getUrlOfFilteredImage(self::SOURCE_IMAGE, self::FILTER, $resolver, true));
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
        $filteredBinary = $this
            ->getMockBuilder(BinaryInterface::class)
            ->getMock();

        $service = $this->createFilterService($webpGenerate);

        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->withConsecutive(
                [self::SOURCE_IMAGE, self::FILTER, $resolver],
                [self::WEBP_IMAGE, self::FILTER, $resolver]
            )
            ->willReturn(false);
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('store')
            ->withConsecutive(
                [$filteredBinary, self::SOURCE_IMAGE, self::FILTER, $resolver],
                [$filteredBinary, self::WEBP_IMAGE, self::FILTER, $resolver]
            );
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('resolve')
            ->with(self::SOURCE_IMAGE, self::FILTER, $resolver)
            ->willReturn($url);

        $this->dataManager
            ->expects($this->atLeastOnce())
            ->method('find')
            ->with(self::FILTER, self::SOURCE_IMAGE)
            ->willReturn($binary);

        $this->filterManager
            ->expects($this->atLeastOnce())
            ->method('applyFilter')
            ->withConsecutive(
                [$binary, self::FILTER, []],
                [$binary, self::FILTER, [
                    'format' => 'webp',
                ] + self::WEBP_OPTIONS]
            )
            ->willReturn($binary);

        $this->assertSame($url, $service->getUrlOfFilteredImage(self::SOURCE_IMAGE, self::FILTER, $resolver));
    }

    /**
     * @dataProvider provideWebpGeneration
     */
    public function testGetUrlOfFilteredImageNotStoredWebpSupported(bool $webpGenerate): void
    {
        $resolver = null;
        $url = 'https://example.com/cache'.self::WEBP_IMAGE;
        $binary = $this
            ->getMockBuilder(BinaryInterface::class)
            ->getMock();
        $filteredBinary = $this
            ->getMockBuilder(BinaryInterface::class)
            ->getMock();

        $service = $this->createFilterService($webpGenerate);

        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->withConsecutive(
                [self::SOURCE_IMAGE, self::FILTER, $resolver],
                [self::WEBP_IMAGE, self::FILTER, $resolver]
            )
            ->willReturn(false);
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('store')
            ->withConsecutive(
                [$filteredBinary, self::SOURCE_IMAGE, self::FILTER, $resolver],
                [$filteredBinary, self::WEBP_IMAGE, self::FILTER, $resolver]
            );
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('resolve')
            ->with(self::WEBP_IMAGE, self::FILTER, $resolver)
            ->willReturn($url);

        $this->dataManager
            ->expects($this->atLeastOnce())
            ->method('find')
            ->with(self::FILTER, self::SOURCE_IMAGE)
            ->willReturn($binary);

        $this->filterManager
            ->expects($this->atLeastOnce())
            ->method('applyFilter')
            ->withConsecutive(
                [$binary, self::FILTER, []],
                [$binary, self::FILTER, [
                    'format' => 'webp',
                ] + self::WEBP_OPTIONS]
            )
            ->willReturn($binary);

        $this->assertSame($url, $service->getUrlOfFilteredImage(self::SOURCE_IMAGE, self::FILTER, $resolver, true));
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
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->withConsecutive(
                [self::SOURCE_IMAGE, self::FILTER, $resolver],
                [self::WEBP_IMAGE, self::FILTER, $resolver]
            )
            ->willReturn(false);

        $this->dataManager
            ->expects($this->atLeastOnce())
            ->method('find')
            ->with(self::FILTER, self::SOURCE_IMAGE)
            ->willReturn($binary);

        $this->filterManager
            ->expects($this->atLeastOnce())
            ->method('applyFilter')
            ->withConsecutive(
                [$binary, self::FILTER, []],
                [$binary, self::FILTER, [
                        'format' => 'webp',
                    ] + self::WEBP_OPTIONS]
            )
            ->willThrowException($exception);

        $this->logger
            ->expects($this->atLeastOnce())
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

        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->withConsecutive(
                [self::RUNTIME_IMAGE, self::FILTER, $resolver],
                [self::RUNTIME_WEBP_IMAGE, self::FILTER, $resolver]
            )
            ->willReturn(true);
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('resolve')
            ->with(self::RUNTIME_IMAGE, self::FILTER, $resolver)
            ->willReturn($url);
        $this->cacheManager
            ->expects($this->never())
            ->method('store');
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('getRuntimePath')
            ->with(self::SOURCE_IMAGE, self::RUNTIME_FILTERS)
            ->willReturn(self::RUNTIME_IMAGE);

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
    public function testGetUrlOfFilteredImageWithRuntimeFiltersWebpSupported(bool $webpGenerate): void
    {
        $resolver = null;
        $url = 'https://example.com/cache'.self::RUNTIME_WEBP_IMAGE;

        $service = $this->createFilterService($webpGenerate);

        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->withConsecutive(
                [self::RUNTIME_IMAGE, self::FILTER, $resolver],
                [self::RUNTIME_WEBP_IMAGE, self::FILTER, $resolver]
            )
            ->willReturn(true);
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('resolve')
            ->with(self::RUNTIME_WEBP_IMAGE, self::FILTER, $resolver)
            ->willReturn($url);
        $this->cacheManager
            ->expects($this->never())
            ->method('store');
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('getRuntimePath')
            ->with(self::SOURCE_IMAGE, self::RUNTIME_FILTERS)
            ->willReturn(self::RUNTIME_IMAGE);

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
            $resolver,
            true
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
        $filteredBinary = $this
            ->getMockBuilder(BinaryInterface::class)
            ->getMock();

        $service = $this->createFilterService($webpGenerate);

        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->withConsecutive(
                [self::RUNTIME_IMAGE, self::FILTER, $resolver],
                [self::RUNTIME_WEBP_IMAGE, self::FILTER, $resolver]
            )
            ->willReturn(false);
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('store')
            ->withConsecutive(
                [$filteredBinary, self::RUNTIME_IMAGE, self::FILTER, $resolver],
                [$filteredBinary, self::RUNTIME_WEBP_IMAGE, self::FILTER, $resolver]
            );
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('resolve')
            ->with(self::RUNTIME_IMAGE, self::FILTER, $resolver)
            ->willReturn($url);
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('getRuntimePath')
            ->with(self::SOURCE_IMAGE, self::RUNTIME_FILTERS)
            ->willReturn(self::RUNTIME_IMAGE);

        $this->dataManager
            ->expects($this->atLeastOnce())
            ->method('find')
            ->with(self::FILTER, self::SOURCE_IMAGE)
            ->willReturn($binary);

        $this->filterManager
            ->expects($this->atLeastOnce())
            ->method('applyFilter')
            ->withConsecutive(
                [$binary, self::FILTER, $runtimeOptions],
                [$binary, self::FILTER, [
                    'format' => 'webp',
                ] + self::WEBP_OPTIONS + $runtimeOptions]
            )
            ->willReturn($binary);

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
    public function testGetUrlOfFilteredImageWithRuntimeFiltersNotStoredWebpSupported(bool $webpGenerate): void
    {
        $resolver = null;
        $url = 'https://example.com/cache'.self::RUNTIME_WEBP_IMAGE;
        $runtimeOptions = [
            'filters' => self::RUNTIME_FILTERS,
        ];
        $binary = $this
            ->getMockBuilder(BinaryInterface::class)
            ->getMock();
        $filteredBinary = $this
            ->getMockBuilder(BinaryInterface::class)
            ->getMock();

        $service = $this->createFilterService($webpGenerate);

        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->withConsecutive(
                [self::RUNTIME_IMAGE, self::FILTER, $resolver],
                [self::RUNTIME_WEBP_IMAGE, self::FILTER, $resolver]
            )
            ->willReturn(false);
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('store')
            ->withConsecutive(
                [$filteredBinary, self::RUNTIME_IMAGE, self::FILTER, $resolver],
                [$filteredBinary, self::RUNTIME_WEBP_IMAGE, self::FILTER, $resolver]
            );
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('resolve')
            ->with(self::RUNTIME_WEBP_IMAGE, self::FILTER, $resolver)
            ->willReturn($url);
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('getRuntimePath')
            ->with(self::SOURCE_IMAGE, self::RUNTIME_FILTERS)
            ->willReturn(self::RUNTIME_IMAGE);

        $this->dataManager
            ->expects($this->atLeastOnce())
            ->method('find')
            ->with(self::FILTER, self::SOURCE_IMAGE)
            ->willReturn($binary);

        $this->filterManager
            ->expects($this->atLeastOnce())
            ->method('applyFilter')
            ->withConsecutive(
                [$binary, self::FILTER, $runtimeOptions],
                [$binary, self::FILTER, [
                    'format' => 'webp',
                ] + self::WEBP_OPTIONS + $runtimeOptions]
            )
            ->willReturn($binary);

        $result = $service->getUrlOfFilteredImageWithRuntimeFilters(
            self::SOURCE_IMAGE,
            self::FILTER,
            self::RUNTIME_FILTERS,
            $resolver,
            true
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
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->withConsecutive(
                [self::RUNTIME_IMAGE, self::FILTER, $resolver],
                [self::RUNTIME_WEBP_IMAGE, self::FILTER, $resolver]
            )
            ->willReturn(false);
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('getRuntimePath')
            ->with(self::SOURCE_IMAGE, self::RUNTIME_FILTERS)
            ->willReturn(self::RUNTIME_IMAGE);

        $this->dataManager
            ->expects($this->atLeastOnce())
            ->method('find')
            ->with(self::FILTER, self::SOURCE_IMAGE)
            ->willReturn($binary);

        $this->filterManager
            ->expects($this->atLeastOnce())
            ->method('applyFilter')
            ->withConsecutive(
                [$binary, self::FILTER, $runtimeOptions],
                [$binary, self::FILTER, [
                        'format' => 'webp',
                    ] + self::WEBP_OPTIONS + $runtimeOptions]
            )
            ->willThrowException($exception);

        $this->logger
            ->expects($this->atLeastOnce())
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
}
