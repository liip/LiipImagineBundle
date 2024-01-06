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

use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Binary\MimeTypeGuesserInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\PostProcessorInterface;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\FilterManager
 */
class FilterManagerTest extends AbstractTest
{
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
}
