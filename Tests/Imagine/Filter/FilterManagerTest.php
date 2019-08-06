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

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\FilterManager
 */
class FilterManagerTest extends AbstractTest
{
    public function testThrowsIfNoLoadersAddedForFilterOnApplyFilter()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not find filter(s): "thumbnail"');

        $config = $this->createFilterConfigurationMock();
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
            $this->createImagineInterfaceMock(),
            $this->createMimeTypeGuesserInterfaceMock()
        );

        $filterManager->applyFilter($binary, 'thumbnail');
    }

    public function testReturnFilteredBinaryWithExpectedContentOnApplyFilter()
    {
        $originalContent = 'aOriginalContent';
        $expectedFilteredContent = 'theFilteredContent';

        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createFilterConfigurationMock();
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

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn($expectedFilteredContent);

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->with($originalContent)
            ->willReturn($image);

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createMimeTypeGuesserInterfaceMock()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf(Binary::class, $filteredBinary);
        $this->assertSame($expectedFilteredContent, $filteredBinary->getContent());
    }

    public function testReturnFilteredBinaryWithFormatOfOriginalBinaryOnApplyFilter()
    {
        $originalContent = 'aOriginalContent';
        $expectedFormat = 'png';

        $binary = new Binary($originalContent, 'image/png', $expectedFormat);

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createFilterConfigurationMock();
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

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn('aFilteredContent');

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createMimeTypeGuesserInterfaceMock()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf(Binary::class, $filteredBinary);
        $this->assertSame($expectedFormat, $filteredBinary->getFormat());
    }

    public function testReturnFilteredBinaryWithCustomFormatIfSetOnApplyFilter()
    {
        $originalContent = 'aOriginalContent';
        $originalFormat = 'png';
        $expectedFormat = 'jpg';

        $binary = new Binary($originalContent, 'image/png', $originalFormat);

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createFilterConfigurationMock();
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

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn('aFilteredContent');

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createMimeTypeGuesserInterfaceMock()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf(Binary::class, $filteredBinary);
        $this->assertSame($expectedFormat, $filteredBinary->getFormat());
    }

    public function testReturnFilteredBinaryWithMimeTypeOfOriginalBinaryOnApplyFilter()
    {
        $originalContent = 'aOriginalContent';
        $expectedMimeType = 'image/png';

        $binary = new Binary($originalContent, $expectedMimeType, 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createFilterConfigurationMock();
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

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn('aFilteredContent');

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $loader = $this->createFilterLoaderInterfaceMock();
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

    public function testReturnFilteredBinaryWithMimeTypeOfCustomFormatIfSetOnApplyFilter()
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

        $config = $this->createFilterConfigurationMock();
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

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn($expectedContent);

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($expectedContent)
            ->willReturn($expectedMimeType);

        $loader = $this->createFilterLoaderInterfaceMock();
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

    public function testAltersQualityOnApplyFilter()
    {
        $originalContent = 'aOriginalContent';
        $expectedQuality = 80;

        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createFilterConfigurationMock();
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

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', ['quality' => $expectedQuality])
            ->willReturn('aFilteredContent');

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createMimeTypeGuesserInterfaceMock()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $this->assertInstanceOf(Binary::class, $filterManager->applyFilter($binary, 'thumbnail'));
    }

    public function testAlters100QualityIfNotSetOnApplyFilter()
    {
        $originalContent = 'aOriginalContent';
        $expectedQuality = 100;

        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createFilterConfigurationMock();
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

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', ['quality' => $expectedQuality])
            ->willReturn('aFilteredContent');

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createMimeTypeGuesserInterfaceMock()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $this->assertInstanceOf(Binary::class, $filterManager->applyFilter($binary, 'thumbnail'));
    }

    public function testMergeRuntimeConfigWithOneFromFilterConfigurationOnApplyFilter()
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

        $config = $this->createFilterConfigurationMock();
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

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn('aFilteredContent');

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbMergedConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createMimeTypeGuesserInterfaceMock()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $this->assertInstanceOf(
            Binary::class,
            $filterManager->applyFilter($binary, 'thumbnail', $runtimeConfig)
        );
    }

    public function testThrowsIfNoLoadersAddedForFilterOnApply()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not find filter(s): "thumbnail"');

        $binary = new Binary('aContent', 'image/png', 'png');

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $this->createImagineInterfaceMock(),
            $this->createMimeTypeGuesserInterfaceMock()
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

    public function testReturnFilteredBinaryWithExpectedContentOnApply()
    {
        $originalContent = 'aOriginalContent';
        $expectedFilteredContent = 'theFilteredContent';

        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn($expectedFilteredContent);

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->with($originalContent)
            ->willReturn($image);

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->createMimeTypeGuesserInterfaceMock()
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

    public function testReturnFilteredBinaryWithFormatOfOriginalBinaryOnApply()
    {
        $originalContent = 'aOriginalContent';
        $expectedFormat = 'png';

        $binary = new Binary($originalContent, 'image/png', $expectedFormat);

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn('aFilteredContent');

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->createMimeTypeGuesserInterfaceMock()
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

    public function testReturnFilteredBinaryWithCustomFormatIfSetOnApply()
    {
        $originalContent = 'aOriginalContent';
        $originalFormat = 'png';
        $expectedFormat = 'jpg';

        $binary = new Binary($originalContent, 'image/png', $originalFormat);

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn('aFilteredContent');

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->createMimeTypeGuesserInterfaceMock()
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

    public function testReturnFilteredBinaryWithMimeTypeOfOriginalBinaryOnApply()
    {
        $originalContent = 'aOriginalContent';
        $expectedMimeType = 'image/png';

        $binary = new Binary($originalContent, $expectedMimeType, 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn('aFilteredContent');

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
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

    public function testReturnFilteredBinaryWithMimeTypeOfCustomFormatIfSetOnApply()
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

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn($expectedContent);

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($expectedContent)
            ->willReturn($expectedMimeType);

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
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

    public function testAltersQualityOnApply()
    {
        $originalContent = 'aOriginalContent';
        $expectedQuality = 80;

        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', ['quality' => $expectedQuality])
            ->willReturn('aFilteredContent');

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->createMimeTypeGuesserInterfaceMock()
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

    public function testAlters100QualityIfNotSetOnApply()
    {
        $originalContent = 'aOriginalContent';
        $expectedQuality = 100;

        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', ['quality' => $expectedQuality])
            ->willReturn('aFilteredContent');

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($image);

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->createMimeTypeGuesserInterfaceMock()
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

    public function testApplyPostProcessor()
    {
        $originalContent = 'aContent';
        $expectedPostProcessedContent = 'postProcessedContent';
        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createFilterConfigurationMock();
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

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn($originalContent);

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->with($originalContent)
            ->willReturn($image);

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $processedBinary = new Binary($expectedPostProcessedContent, 'image/png', 'png');

        $postProcessor = $this->createPostProcessorInterfaceMock();
        $postProcessor
            ->expects($this->once())
            ->method('process')
            ->with($binary)
            ->willReturn($processedBinary);

        $filterManager = new FilterManager(
            $config,
            $imagineMock,
            $this->createMimeTypeGuesserInterfaceMock()
        );
        $filterManager->addLoader('thumbnail', $loader);
        $filterManager->addPostProcessor('foo', $postProcessor);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');
        $this->assertInstanceOf(Binary::class, $filteredBinary);
        $this->assertSame($expectedPostProcessedContent, $filteredBinary->getContent());
    }

    public function testThrowsIfNoPostProcessorAddedForFilterOnApplyFilter()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not find post processor(s): "foo"');

        $originalContent = 'aContent';
        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createFilterConfigurationMock();
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

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->willReturn($originalContent);

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->with($originalContent)
            ->willReturn($image);

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->willReturnArgument(0);

        $filterManager = new FilterManager(
            $config,
            $imagineMock,
            $this->createMimeTypeGuesserInterfaceMock()
        );

        $filterManager->addLoader('thumbnail', $loader);
        $filterManager->applyFilter($binary, 'thumbnail');
    }

    public function testApplyPostProcessorsWhenNotDefined()
    {
        $binary = $this->getMockBuilder(BinaryInterface::class)->getMock();
        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $this->createImagineInterfaceMock(),
            $this->createMimeTypeGuesserInterfaceMock()
        );

        $this->assertSame($binary, $filterManager->applyPostProcessors($binary, []));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoaderInterface
     */
    protected function createFilterLoaderInterfaceMock()
    {
        return $this->createObjectMock(LoaderInterface::class);
    }
}
