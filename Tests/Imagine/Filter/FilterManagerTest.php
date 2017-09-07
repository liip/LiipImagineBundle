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

use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\FilterManager
 */
class FilterManagerTest extends AbstractTest
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Could not find filter loader for "thumbnail" filter type
     */
    public function testThrowsIfNoLoadersAddedForFilterOnApplyFilter()
    {
        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'filters' => array(
                    'thumbnail' => array(
                        'size' => array(180, 180),
                        'mode' => 'outbound',
                    ),
                ),
                'post_processors' => array(),
            )));

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

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'filters' => array(
                    'thumbnail' => $thumbConfig,
                ),
                'post_processors' => array(),
            )));

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($expectedFilteredContent));

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->with($originalContent)
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createMimeTypeGuesserInterfaceMock()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf('\Liip\ImagineBundle\Model\Binary', $filteredBinary);
        $this->assertEquals($expectedFilteredContent, $filteredBinary->getContent());
    }

    public function testReturnFilteredBinaryWithFormatOfOriginalBinaryOnApplyFilter()
    {
        $originalContent = 'aOriginalContent';
        $expectedFormat = 'png';

        $binary = new Binary($originalContent, 'image/png', $expectedFormat);

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'filters' => array(
                    'thumbnail' => $thumbConfig,
                ),
                'post_processors' => array(),
            )));

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('aFilteredContent'));

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createMimeTypeGuesserInterfaceMock()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf('\Liip\ImagineBundle\Model\Binary', $filteredBinary);
        $this->assertEquals($expectedFormat, $filteredBinary->getFormat());
    }

    public function testReturnFilteredBinaryWithCustomFormatIfSetOnApplyFilter()
    {
        $originalContent = 'aOriginalContent';
        $originalFormat = 'png';
        $expectedFormat = 'jpg';

        $binary = new Binary($originalContent, 'image/png', $originalFormat);

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'format' => $expectedFormat,
                'filters' => array(
                    'thumbnail' => $thumbConfig,
                ),
                'post_processors' => array(),
            )));

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('aFilteredContent'));

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createMimeTypeGuesserInterfaceMock()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf('\Liip\ImagineBundle\Model\Binary', $filteredBinary);
        $this->assertEquals($expectedFormat, $filteredBinary->getFormat());
    }

    public function testReturnFilteredBinaryWithMimeTypeOfOriginalBinaryOnApplyFilter()
    {
        $originalContent = 'aOriginalContent';
        $expectedMimeType = 'image/png';

        $binary = new Binary($originalContent, $expectedMimeType, 'png');

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'filters' => array(
                    'thumbnail' => $thumbConfig,
                ),
                'post_processors' => array(),
            )));

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('aFilteredContent'));

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $mimeTypeGuesser
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf('\Liip\ImagineBundle\Model\Binary', $filteredBinary);
        $this->assertEquals($expectedMimeType, $filteredBinary->getMimeType());
    }

    public function testReturnFilteredBinaryWithMimeTypeOfCustomFormatIfSetOnApplyFilter()
    {
        $originalContent = 'aOriginalContent';
        $originalMimeType = 'image/png';
        $expectedContent = 'aFilteredContent';
        $expectedMimeType = 'image/jpeg';

        $binary = new Binary($originalContent, $originalMimeType, 'png');

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'format' => 'jpg',
                'filters' => array(
                    'thumbnail' => $thumbConfig,
                ),
                'post_processors' => array(),
            )));

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($expectedContent));

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($expectedContent)
            ->will($this->returnValue($expectedMimeType));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $mimeTypeGuesser
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf('\Liip\ImagineBundle\Model\Binary', $filteredBinary);
        $this->assertEquals($expectedMimeType, $filteredBinary->getMimeType());
    }

    public function testAltersQualityOnApplyFilter()
    {
        $originalContent = 'aOriginalContent';
        $expectedQuality = 80;

        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'quality' => $expectedQuality,
                'filters' => array(
                    'thumbnail' => $thumbConfig,
                ),
                'post_processors' => array(),
            )));

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', array('quality' => $expectedQuality))
            ->will($this->returnValue('aFilteredContent'));

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createMimeTypeGuesserInterfaceMock()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $this->assertInstanceOf('\Liip\ImagineBundle\Model\Binary', $filterManager->applyFilter($binary, 'thumbnail'));
    }

    public function testAlters100QualityIfNotSetOnApplyFilter()
    {
        $originalContent = 'aOriginalContent';
        $expectedQuality = 100;

        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'filters' => array(
                    'thumbnail' => $thumbConfig,
                ),
                'post_processors' => array(),
            )));

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', array('quality' => $expectedQuality))
            ->will($this->returnValue('aFilteredContent'));

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createMimeTypeGuesserInterfaceMock()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $this->assertInstanceOf('\Liip\ImagineBundle\Model\Binary', $filterManager->applyFilter($binary, 'thumbnail'));
    }

    public function testMergeRuntimeConfigWithOneFromFilterConfigurationOnApplyFilter()
    {
        $binary = new Binary('aContent', 'image/png', 'png');

        $runtimeConfig = array(
            'filters' => array(
                'thumbnail' => array(
                    'size' => array(100, 100),
                ),
            ),
            'post_processors' => array(),
        );

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $thumbMergedConfig = array(
            'size' => array(100, 100),
            'mode' => 'outbound',
        );

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'filters' => array(
                    'thumbnail' => $thumbConfig,
                ),
                'post_processors' => array(),
            )));

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('aFilteredContent'));

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbMergedConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createMimeTypeGuesserInterfaceMock()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $this->assertInstanceOf(
            '\Liip\ImagineBundle\Model\Binary',
            $filterManager->applyFilter($binary, 'thumbnail', $runtimeConfig)
        );
    }
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Could not find filter loader for "thumbnail" filter type
     */
    public function testThrowsIfNoLoadersAddedForFilterOnApply()
    {
        $binary = new Binary('aContent', 'image/png', 'png');

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $this->createImagineInterfaceMock(),
            $this->createMimeTypeGuesserInterfaceMock()
        );

        $filterManager->apply($binary, array(
            'filters' => array(
                'thumbnail' => array(
                    'size' => array(180, 180),
                    'mode' => 'outbound',
                ),
            ),
        ));
    }

    public function testReturnFilteredBinaryWithExpectedContentOnApply()
    {
        $originalContent = 'aOriginalContent';
        $expectedFilteredContent = 'theFilteredContent';

        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($expectedFilteredContent));

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->with($originalContent)
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->createMimeTypeGuesserInterfaceMock()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, array(
            'filters' => array(
                'thumbnail' => $thumbConfig,
            ),
            'post_processors' => array(),
        ));

        $this->assertInstanceOf('\Liip\ImagineBundle\Model\Binary', $filteredBinary);
        $this->assertEquals($expectedFilteredContent, $filteredBinary->getContent());
    }

    public function testReturnFilteredBinaryWithFormatOfOriginalBinaryOnApply()
    {
        $originalContent = 'aOriginalContent';
        $expectedFormat = 'png';

        $binary = new Binary($originalContent, 'image/png', $expectedFormat);

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('aFilteredContent'));

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->createMimeTypeGuesserInterfaceMock()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, array(
            'filters' => array(
                'thumbnail' => $thumbConfig,
            ),
            'post_processors' => array(),
        ));

        $this->assertInstanceOf('\Liip\ImagineBundle\Model\Binary', $filteredBinary);
        $this->assertEquals($expectedFormat, $filteredBinary->getFormat());
    }

    public function testReturnFilteredBinaryWithCustomFormatIfSetOnApply()
    {
        $originalContent = 'aOriginalContent';
        $originalFormat = 'png';
        $expectedFormat = 'jpg';

        $binary = new Binary($originalContent, 'image/png', $originalFormat);

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('aFilteredContent'));

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->createMimeTypeGuesserInterfaceMock()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, array(
            'format' => $expectedFormat,
            'filters' => array(
                'thumbnail' => $thumbConfig,
            ),
            'post_processors' => array(),
        ));

        $this->assertInstanceOf('\Liip\ImagineBundle\Model\Binary', $filteredBinary);
        $this->assertEquals($expectedFormat, $filteredBinary->getFormat());
    }

    public function testReturnFilteredBinaryWithMimeTypeOfOriginalBinaryOnApply()
    {
        $originalContent = 'aOriginalContent';
        $expectedMimeType = 'image/png';

        $binary = new Binary($originalContent, $expectedMimeType, 'png');

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('aFilteredContent'));

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $mimeTypeGuesser
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, array(
            'filters' => array(
                'thumbnail' => $thumbConfig,
            ),
            'post_processors' => array(),
        ));

        $this->assertInstanceOf('\Liip\ImagineBundle\Model\Binary', $filteredBinary);
        $this->assertEquals($expectedMimeType, $filteredBinary->getMimeType());
    }

    public function testReturnFilteredBinaryWithMimeTypeOfCustomFormatIfSetOnApply()
    {
        $originalContent = 'aOriginalContent';
        $originalMimeType = 'image/png';
        $expectedContent = 'aFilteredContent';
        $expectedMimeType = 'image/jpeg';

        $binary = new Binary($originalContent, $originalMimeType, 'png');

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($expectedContent));

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($expectedContent)
            ->will($this->returnValue($expectedMimeType));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $mimeTypeGuesser
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, array(
            'format' => 'jpg',
            'filters' => array(
                'thumbnail' => $thumbConfig,
            ),
            'post_processors' => array(),
        ));

        $this->assertInstanceOf('\Liip\ImagineBundle\Model\Binary', $filteredBinary);
        $this->assertEquals($expectedMimeType, $filteredBinary->getMimeType());
    }

    public function testAltersQualityOnApply()
    {
        $originalContent = 'aOriginalContent';
        $expectedQuality = 80;

        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', array('quality' => $expectedQuality))
            ->will($this->returnValue('aFilteredContent'));

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->createMimeTypeGuesserInterfaceMock()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, array(
            'quality' => $expectedQuality,
            'filters' => array(
                'thumbnail' => $thumbConfig,
            ),
            'post_processors' => array(),
        ));

        $this->assertInstanceOf('\Liip\ImagineBundle\Model\Binary', $filteredBinary);
    }

    public function testAlters100QualityIfNotSetOnApply()
    {
        $originalContent = 'aOriginalContent';
        $expectedQuality = 100;

        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', array('quality' => $expectedQuality))
            ->will($this->returnValue('aFilteredContent'));

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->createMimeTypeGuesserInterfaceMock()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, array(
            'filters' => array(
                'thumbnail' => $thumbConfig,
            ),
            'post_processors' => array(),
        ));

        $this->assertInstanceOf('\Liip\ImagineBundle\Model\Binary', $filteredBinary);
    }

    public function testApplyPostProcessor()
    {
        $originalContent = 'aContent';
        $expectedPostProcessedContent = 'postProcessedContent';
        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'filters' => array(
                    'thumbnail' => $thumbConfig,
                ),
                'post_processors' => array(
                    'foo' => array(),
                ),
            )));

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($originalContent));

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->with($originalContent)
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $processedBinary = new Binary($expectedPostProcessedContent, 'image/png', 'png');

        $postProcessor = $this->createPostProcessorInterfaceMock();
        $postProcessor
            ->expects($this->once())
            ->method('process')
            ->with($binary)
            ->will($this->returnValue($processedBinary));

        $filterManager = new FilterManager(
            $config,
            $imagineMock,
            $this->createMimeTypeGuesserInterfaceMock()
        );
        $filterManager->addLoader('thumbnail', $loader);
        $filterManager->addPostProcessor('foo', $postProcessor);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');
        $this->assertInstanceOf('\Liip\ImagineBundle\Model\Binary', $filteredBinary);
        $this->assertEquals($expectedPostProcessedContent, $filteredBinary->getContent());
    }
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Post-processor "foo" could not be found
     */
    public function testThrowsIfNoPostProcessorAddedForFilterOnApplyFilter()
    {
        $originalContent = 'aContent';
        $binary = new Binary($originalContent, 'image/png', 'png');

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'filters' => array(
                    'thumbnail' => $thumbConfig,
                ),
                'post_processors' => array(
                    'foo' => array(),
                ),
            )));

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($originalContent));

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->with($originalContent)
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

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
        $binary = $this->getMockBuilder('\Liip\ImagineBundle\Binary\BinaryInterface')->getMock();
        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $this->createImagineInterfaceMock(),
            $this->createMimeTypeGuesserInterfaceMock()
        );

        $this->assertSame($binary, $filterManager->applyPostProcessors($binary, array()));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoaderInterface
     */
    protected function createFilterLoaderInterfaceMock()
    {
        return $this->createObjectMock('\Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface');
    }
}
