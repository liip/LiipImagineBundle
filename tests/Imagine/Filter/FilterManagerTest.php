<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Filter;

use Imagine\Gd\Imagine as GdImagine;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Vips\Imagine as VipsImagine;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Binary\MimeTypeGuesserInterface;
use Liip\ImagineBundle\Events\FilterEvent;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\PostProcessorInterface;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\FilterManager
 * @covers \Liip\ImagineBundle\Events\FilterEvent
 */
class FilterManagerTest extends AbstractTest
{
    private const EVENT_PRE_FILTER = 'liip_imagine.pre_filter';
    private const EVENT_POST_FILTER = 'liip_imagine.post_filter';
    private const EVENT_PRE_POST_PROCESSOR = 'liip_imagine.pre_post_processor';
    private const EVENT_POST_POST_PROCESSOR = 'liip_imagine.post_post_processor';
    private const VIPS_JPEG_QUALITY = 60;
    private const VIPS_QUALITY = 80;

    public function testThrowsIfNoLoadersAddedForFilterOnApplyFilter(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not find filter(s): "thumbnail"');

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'filters' => [
                    'thumbnail' => [
                        'size' => [180, 180],
                        'mode' => 'outbound',
                    ],
                ],
                'post_processors' => [],
            ]);

        $binary = new Binary('aContent', 'image/png', 'png');

        $filterManager = new FilterManager(
            $config,
            $this->createMock(ImagineInterface::class),
            $this->createMock(MimeTypeGuesserInterface::class)
        );

        $filterManager->applyFilter($binary, 'thumbnail');
    }

    public function testReturnFilteredBinaryWithExpectedContentOnApplyFilter(): void
    {
        $originalContent = 'aOriginalContent';
        $expectedFilteredContent = 'theFilteredContent';

        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'filters' => [
                    'thumbnail' => $thumbConfig,
                ],
                'post_processors' => [],
            ]);

        $image = $this->createMock(ImageInterface::class);
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn($expectedFilteredContent);

        $imagine = $this->createMock(ImagineInterface::class);
        $imagine
            ->expects($this->once())
            ->method('load')
            ->with($originalContent)
            ->willReturn($image);

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createMock(MimeTypeGuesserInterface::class)
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf(Binary::class, $filteredBinary);
        $this->assertSame($expectedFilteredContent, $filteredBinary->getContent());
    }

    public function testReturnFilteredBinaryWithFormatOfOriginalBinaryOnApplyFilter(): void
    {
        $originalContent = 'aOriginalContent';
        $expectedFormat = 'png';

        $binary = new Binary($originalContent, 'image/png', $expectedFormat);

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'filters' => [
                    'thumbnail' => $thumbConfig,
                ],
                'post_processors' => [],
            ]);

        $image = $this->createMock(ImageInterface::class);
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn('aFilteredContent');

        $imagine = $this->createMock(ImagineInterface::class);
        $imagine
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createMock(MimeTypeGuesserInterface::class)
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf(Binary::class, $filteredBinary);
        $this->assertSame($expectedFormat, $filteredBinary->getFormat());
    }

    public function testReturnFilteredBinaryWithCustomFormatIfSetOnApplyFilter(): void
    {
        $originalContent = 'aOriginalContent';
        $originalFormat = 'png';
        $expectedFormat = 'jpg';

        $binary = new Binary($originalContent, 'image/png', $originalFormat);

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'format' => $expectedFormat,
                'filters' => [
                    'thumbnail' => $thumbConfig,
                ],
                'post_processors' => [],
            ]);

        $image = $this->createMock(ImageInterface::class);
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn('aFilteredContent');

        $imagine = $this->createMock(ImagineInterface::class);
        $imagine
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createMock(MimeTypeGuesserInterface::class)
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf(Binary::class, $filteredBinary);
        $this->assertSame($expectedFormat, $filteredBinary->getFormat());
    }

    public function testReturnFilteredBinaryWithMimeTypeOfOriginalBinaryOnApplyFilter(): void
    {
        $originalContent = 'aOriginalContent';
        $expectedMimeType = 'image/png';

        $binary = new Binary($originalContent, $expectedMimeType, 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'filters' => [
                    'thumbnail' => $thumbConfig,
                ],
                'post_processors' => [],
            ]);

        $image = $this->createMock(ImageInterface::class);
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn('aFilteredContent');

        $imagine = $this->createMock(ImagineInterface::class);
        $imagine
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $mimeTypeGuesser = $this->createMock(MimeTypeGuesserInterface::class);
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $mimeTypeGuesser
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf(Binary::class, $filteredBinary);
        $this->assertSame($expectedMimeType, $filteredBinary->getMimeType());
    }

    public function testReturnFilteredBinaryWithMimeTypeOfCustomFormatIfSetOnApplyFilter(): void
    {
        $originalContent = 'aOriginalContent';
        $originalMimeType = 'image/png';
        $expectedContent = 'aFilteredContent';
        $expectedMimeType = 'image/jpeg';

        $binary = new Binary($originalContent, $originalMimeType, 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'format' => 'jpg',
                'filters' => [
                    'thumbnail' => $thumbConfig,
                ],
                'post_processors' => [],
            ]);

        $image = $this->createMock(ImageInterface::class);
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn($expectedContent);

        $imagine = $this->createMock(ImagineInterface::class);
        $imagine
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $mimeTypeGuesser = $this->createMock(MimeTypeGuesserInterface::class);
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($expectedContent)
            ->willReturn($expectedMimeType);

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $mimeTypeGuesser
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf(Binary::class, $filteredBinary);
        $this->assertSame($expectedMimeType, $filteredBinary->getMimeType());
    }

    public function testAltersQualityOnApplyFilter(): void
    {
        $originalContent = 'aOriginalContent';
        $expectedQuality = 80;

        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'quality' => $expectedQuality,
                'filters' => [
                    'thumbnail' => $thumbConfig,
                ],
                'post_processors' => [],
            ]);

        $image = $this->createMock(ImageInterface::class);
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', ['quality' => $expectedQuality])
            ->willReturn('aFilteredContent');

        $imagine = $this->createMock(ImagineInterface::class);
        $imagine
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createMock(MimeTypeGuesserInterface::class)
        );
        $filterManager->addLoader('thumbnail', $loader);

        $this->assertInstanceOf(Binary::class, $filterManager->applyFilter($binary, 'thumbnail'));
    }

    public function testMapsQualityToVipsFormatOptionsOnApply(): void
    {
        if (!class_exists(VipsImagine::class)) {
            class_alias(GdImagine::class, VipsImagine::class);
        }

        $binary = new Binary('aOriginalContent', 'image/png', 'png');
        $image = $this->createMock(ImageInterface::class);
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', [
                'quality' => self::VIPS_QUALITY,
                'jpeg_quality' => self::VIPS_JPEG_QUALITY,
                'png_quality' => self::VIPS_QUALITY,
                'webp_quality' => self::VIPS_QUALITY,
                'heif_quality' => self::VIPS_QUALITY,
                'avif_quality' => self::VIPS_QUALITY,
                'jxl_quality' => self::VIPS_QUALITY,
            ])
            ->willReturn('aFilteredContent');

        $imagine = $this->createMock(VipsImagine::class);
        $imagine
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $filterManager = new FilterManager(
            $this->createMock(FilterConfiguration::class),
            $imagine,
            $this->createMock(MimeTypeGuesserInterface::class)
        );

        $this->assertInstanceOf(Binary::class, $filterManager->apply($binary, [
            'quality' => self::VIPS_QUALITY,
            'jpeg_quality' => self::VIPS_JPEG_QUALITY,
            'post_processors' => [],
        ]));
    }

    public function testAlters100QualityIfNotSetOnApplyFilter(): void
    {
        $originalContent = 'aOriginalContent';
        $expectedQuality = 100;

        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'filters' => [
                    'thumbnail' => $thumbConfig,
                ],
                'post_processors' => [],
            ]);

        $image = $this->createMock(ImageInterface::class);
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', ['quality' => $expectedQuality])
            ->willReturn('aFilteredContent');

        $imagine = $this->createMock(ImagineInterface::class);
        $imagine
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createMock(MimeTypeGuesserInterface::class)
        );
        $filterManager->addLoader('thumbnail', $loader);

        $this->assertInstanceOf(Binary::class, $filterManager->applyFilter($binary, 'thumbnail'));
    }

    public function testMergeRuntimeConfigWithOneFromFilterConfigurationOnApplyFilter(): void
    {
        $binary = new Binary('aContent', 'image/png', 'png');

        $runtimeConfig = [
            'filters' => [
                'thumbnail' => [
                    'size' => [100, 100],
                ],
            ],
            'post_processors' => [],
        ];

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $thumbMergedConfig = [
            'size' => [100, 100],
            'mode' => 'outbound',
        ];

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'filters' => [
                    'thumbnail' => $thumbConfig,
                ],
                'post_processors' => [],
            ]);

        $image = $this->createMock(ImageInterface::class);
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn('aFilteredContent');

        $imagine = $this->createMock(ImagineInterface::class);
        $imagine
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbMergedConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createMock(MimeTypeGuesserInterface::class)
        );
        $filterManager->addLoader('thumbnail', $loader);

        $this->assertInstanceOf(
            Binary::class,
            $filterManager->applyFilter($binary, 'thumbnail', $runtimeConfig)
        );
    }

    public function testThrowsIfNoLoadersAddedForFilterOnApply(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not find filter(s): "thumbnail"');

        $binary = new Binary('aContent', 'image/png', 'png');

        $filterManager = new FilterManager(
            $this->createMock(FilterConfiguration::class),
            $this->createMock(ImagineInterface::class),
            $this->createMock(MimeTypeGuesserInterface::class)
        );

        $filterManager->apply($binary, [
            'filters' => [
                'thumbnail' => [
                    'size' => [180, 180],
                    'mode' => 'outbound',
                ],
            ],
        ]);
    }

    public function testReturnFilteredBinaryWithExpectedContentOnApply(): void
    {
        $originalContent = 'aOriginalContent';
        $expectedFilteredContent = 'theFilteredContent';

        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->createMock(ImageInterface::class);
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn($expectedFilteredContent);

        $imagineMock = $this->createMock(ImagineInterface::class);
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->with($originalContent)
            ->willReturn($image);

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $this->createMock(FilterConfiguration::class),
            $imagineMock,
            $this->createMock(MimeTypeGuesserInterface::class)
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, [
            'filters' => [
                'thumbnail' => $thumbConfig,
            ],
            'post_processors' => [],
        ]);

        $this->assertInstanceOf(Binary::class, $filteredBinary);
        $this->assertSame($expectedFilteredContent, $filteredBinary->getContent());
    }

    public function testReturnFilteredBinaryWithFormatOfOriginalBinaryOnApply(): void
    {
        $originalContent = 'aOriginalContent';
        $expectedFormat = 'png';

        $binary = new Binary($originalContent, 'image/png', $expectedFormat);

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->createMock(ImageInterface::class);
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn('aFilteredContent');

        $imagineMock = $this->createMock(ImagineInterface::class);
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $this->createMock(FilterConfiguration::class),
            $imagineMock,
            $this->createMock(MimeTypeGuesserInterface::class)
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, [
            'filters' => [
                'thumbnail' => $thumbConfig,
            ],
            'post_processors' => [],
        ]);

        $this->assertInstanceOf(Binary::class, $filteredBinary);
        $this->assertSame($expectedFormat, $filteredBinary->getFormat());
    }

    public function testReturnFilteredBinaryWithCustomFormatIfSetOnApply(): void
    {
        $originalContent = 'aOriginalContent';
        $originalFormat = 'png';
        $expectedFormat = 'jpg';

        $binary = new Binary($originalContent, 'image/png', $originalFormat);

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->createMock(ImageInterface::class);
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn('aFilteredContent');

        $imagineMock = $this->createMock(ImagineInterface::class);
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $this->createMock(FilterConfiguration::class),
            $imagineMock,
            $this->createMock(MimeTypeGuesserInterface::class)
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, [
            'format' => $expectedFormat,
            'filters' => [
                'thumbnail' => $thumbConfig,
            ],
            'post_processors' => [],
        ]);

        $this->assertInstanceOf(Binary::class, $filteredBinary);
        $this->assertSame($expectedFormat, $filteredBinary->getFormat());
    }

    public function testReturnFilteredBinaryWithMimeTypeOfOriginalBinaryOnApply(): void
    {
        $originalContent = 'aOriginalContent';
        $expectedMimeType = 'image/png';

        $binary = new Binary($originalContent, $expectedMimeType, 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->createMock(ImageInterface::class);
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn('aFilteredContent');

        $imagineMock = $this->createMock(ImagineInterface::class);
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $mimeTypeGuesser = $this->createMock(MimeTypeGuesserInterface::class);
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $this->createMock(FilterConfiguration::class),
            $imagineMock,
            $mimeTypeGuesser
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, [
            'filters' => [
                'thumbnail' => $thumbConfig,
            ],
            'post_processors' => [],
        ]);

        $this->assertInstanceOf(Binary::class, $filteredBinary);
        $this->assertSame($expectedMimeType, $filteredBinary->getMimeType());
    }

    public function testReturnFilteredBinaryWithMimeTypeOfCustomFormatIfSetOnApply(): void
    {
        $originalContent = 'aOriginalContent';
        $originalMimeType = 'image/png';
        $expectedContent = 'aFilteredContent';
        $expectedMimeType = 'image/jpeg';

        $binary = new Binary($originalContent, $originalMimeType, 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->createMock(ImageInterface::class);
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn($expectedContent);

        $imagineMock = $this->createMock(ImagineInterface::class);
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $mimeTypeGuesser = $this->createMock(MimeTypeGuesserInterface::class);
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($expectedContent)
            ->willReturn($expectedMimeType);

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $this->createMock(FilterConfiguration::class),
            $imagineMock,
            $mimeTypeGuesser
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, [
            'format' => 'jpg',
            'filters' => [
                'thumbnail' => $thumbConfig,
            ],
            'post_processors' => [],
        ]);

        $this->assertInstanceOf(Binary::class, $filteredBinary);
        $this->assertSame($expectedMimeType, $filteredBinary->getMimeType());
    }

    public function testAltersQualityOnApply(): void
    {
        $originalContent = 'aOriginalContent';
        $expectedQuality = 80;

        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->createMock(ImageInterface::class);
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', ['quality' => $expectedQuality])
            ->willReturn('aFilteredContent');

        $imagineMock = $this->createMock(ImagineInterface::class);
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $this->createMock(FilterConfiguration::class),
            $imagineMock,
            $this->createMock(MimeTypeGuesserInterface::class)
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, [
            'quality' => $expectedQuality,
            'filters' => [
                'thumbnail' => $thumbConfig,
            ],
            'post_processors' => [],
        ]);

        $this->assertInstanceOf(Binary::class, $filteredBinary);
    }

    public function testAlters100QualityIfNotSetOnApply(): void
    {
        $originalContent = 'aOriginalContent';
        $expectedQuality = 100;

        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->createMock(ImageInterface::class);
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', ['quality' => $expectedQuality])
            ->willReturn('aFilteredContent');

        $imagineMock = $this->createMock(ImagineInterface::class);
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $this->createMock(FilterConfiguration::class),
            $imagineMock,
            $this->createMock(MimeTypeGuesserInterface::class)
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, [
            'filters' => [
                'thumbnail' => $thumbConfig,
            ],
            'post_processors' => [],
        ]);

        $this->assertInstanceOf(Binary::class, $filteredBinary);
    }

    public function testApplyPostProcessor(): void
    {
        $originalContent = 'aContent';
        $expectedPostProcessedContent = 'postProcessedContent';
        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'filters' => [
                    'thumbnail' => $thumbConfig,
                ],
                'post_processors' => [
                    'foo' => [],
                ],
            ]);

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->createMock(ImageInterface::class);
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn($originalContent);

        $imagineMock = $this->createMock(ImagineInterface::class);
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->with($originalContent)
            ->willReturn($image);

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $processedBinary = new Binary($expectedPostProcessedContent, 'image/png', 'png');

        $postProcessor = $this->createMock(PostProcessorInterface::class);
        $postProcessor
            ->expects($this->once())
            ->method('process')
            ->with($binary)
            ->willReturn($processedBinary);

        $filterManager = new FilterManager(
            $config,
            $imagineMock,
            $this->createMock(MimeTypeGuesserInterface::class)
        );
        $filterManager->addLoader('thumbnail', $loader);
        $filterManager->addPostProcessor('foo', $postProcessor);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');
        $this->assertInstanceOf(Binary::class, $filteredBinary);
        $this->assertSame($expectedPostProcessedContent, $filteredBinary->getContent());
    }

    public function testThrowsIfNoPostProcessorAddedForFilterOnApplyFilter(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not find post processor(s): "foo"');

        $originalContent = 'aContent';
        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'filters' => [
                    'thumbnail' => $thumbConfig,
                ],
                'post_processors' => [
                    'foo' => [],
                ],
            ]);

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->createMock(ImageInterface::class);
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn($originalContent);

        $imagineMock = $this->createMock(ImagineInterface::class);
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->with($originalContent)
            ->willReturn($image);

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $config,
            $imagineMock,
            $this->createMock(MimeTypeGuesserInterface::class)
        );

        $filterManager->addLoader('thumbnail', $loader);
        $filterManager->applyFilter($binary, 'thumbnail');
    }

    public function testApplyPostProcessorsWhenNotDefined(): void
    {
        $binary = $this->createMock(BinaryInterface::class);
        $filterManager = new FilterManager(
            $this->createMock(FilterConfiguration::class),
            $this->createMock(ImagineInterface::class),
            $this->createMock(MimeTypeGuesserInterface::class)
        );

        $this->assertSame($binary, $filterManager->applyPostProcessors($binary, []));
    }

    public function testDispatchesIndependentEventsAroundFiltersAndPostProcessors(): void
    {
        $sequence = [];
        $events = [];
        $dispatcher = new EventDispatcher();
        foreach ([self::EVENT_PRE_FILTER, self::EVENT_POST_FILTER, self::EVENT_PRE_POST_PROCESSOR, self::EVENT_POST_POST_PROCESSOR] as $eventName) {
            $dispatcher->addListener($eventName, static function (FilterEvent $event) use (&$events, &$sequence, $eventName): void {
                $sequence[] = $eventName;
                $events[$eventName] = $event;

                if (self::EVENT_PRE_FILTER === $eventName || self::EVENT_PRE_POST_PROCESSOR === $eventName) {
                    $event->stopPropagation();
                }
            });
        }

        $image = $this->createMock(ImageInterface::class);
        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->method('load')
            ->willReturnCallback(static function () use (&$sequence, $image) {
                $sequence[] = 'filter';

                return $image;
            });

        $postProcessor = $this->createMock(PostProcessorInterface::class);
        $postProcessor
            ->method('process')
            ->willReturnCallback(static function (BinaryInterface $binary) use (&$sequence): BinaryInterface {
                $sequence[] = 'post_processor';

                return $binary;
            });

        $imagine = $this->createMock(ImagineInterface::class);
        $imagine->method('load')->willReturn($image);
        $image->method('get')->willReturn('filtered');

        $filterManager = new FilterManager(
            $this->createMock(FilterConfiguration::class),
            $imagine,
            $this->createMock(MimeTypeGuesserInterface::class),
            $dispatcher
        );
        $filterManager->addLoader('thumbnail', $loader);
        $filterManager->addPostProcessor('optimizer', $postProcessor);

        $filterManager->apply(new Binary('original', 'image/jpeg', 'jpeg'), [
            'filters' => ['thumbnail' => []],
            'post_processors' => ['optimizer' => []],
        ]);

        $this->assertSame([
            self::EVENT_PRE_FILTER,
            'filter',
            self::EVENT_POST_FILTER,
            self::EVENT_PRE_POST_PROCESSOR,
            'post_processor',
            self::EVENT_POST_POST_PROCESSOR,
        ], $sequence);
        $this->assertSame('thumbnail', $events[self::EVENT_PRE_FILTER]->getName());
        $this->assertSame([], $events[self::EVENT_PRE_FILTER]->getOptions());
        $this->assertNotSame($events[self::EVENT_PRE_FILTER], $events[self::EVENT_POST_FILTER]);
        $this->assertSame('optimizer', $events[self::EVENT_PRE_POST_PROCESSOR]->getName());
        $this->assertSame([], $events[self::EVENT_PRE_POST_PROCESSOR]->getOptions());
        $this->assertNotSame($events[self::EVENT_PRE_POST_PROCESSOR], $events[self::EVENT_POST_POST_PROCESSOR]);
    }
}
