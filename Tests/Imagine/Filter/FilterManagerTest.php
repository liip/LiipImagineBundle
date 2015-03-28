<?php

namespace Liip\ImagineBundle\Tests\Filter;

use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers Liip\ImagineBundle\Imagine\Filter\FilterManager
 */
class FilterManagerTest extends AbstractTest
{
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
            )))
        ;

        $binary = new Binary('aContent', 'image/png', 'png');

        $filterManager = new FilterManager(
            $config,
            $this->createImagineMock(),
            $this->getMockMimeTypeGuesser()
        );

        $this->setExpectedException('InvalidArgumentException', 'Could not find filter loader for "thumbnail" filter type');
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
            )))
        ;

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($expectedFilteredContent))
        ;

        $imagine = $this->createImagineMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->with($originalContent)
            ->will($this->returnValue($image))
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0))
        ;

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->getMockMimeTypeGuesser()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $filteredBinary);
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
            )))
        ;

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('aFilteredContent'))
        ;

        $imagine = $this->createImagineMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image))
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0))
        ;

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->getMockMimeTypeGuesser()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $filteredBinary);
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
            )))
        ;

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('aFilteredContent'))
        ;

        $imagine = $this->createImagineMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image))
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0))
        ;

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->getMockMimeTypeGuesser()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $filteredBinary);
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
            )))
        ;

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('aFilteredContent'))
        ;

        $imagine = $this->createImagineMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image))
        ;

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess')
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0))
        ;

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $mimeTypeGuesser
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $filteredBinary);
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
            )))
        ;

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($expectedContent))
        ;

        $imagine = $this->createImagineMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image))
        ;

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($expectedContent)
            ->will($this->returnValue($expectedMimeType))
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0))
        ;

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $mimeTypeGuesser
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $filteredBinary);
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
            )))
        ;

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', array('quality' => $expectedQuality))
            ->will($this->returnValue('aFilteredContent'))
        ;

        $imagine = $this->createImagineMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image))
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0))
        ;

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->getMockMimeTypeGuesser()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $filterManager->applyFilter($binary, 'thumbnail'));
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
            )))
        ;

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', array('quality' => $expectedQuality))
            ->will($this->returnValue('aFilteredContent'))
        ;

        $imagine = $this->createImagineMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image))
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0))
        ;

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->getMockMimeTypeGuesser()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $filterManager->applyFilter($binary, 'thumbnail'));
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
            )))
        ;

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('aFilteredContent'))
        ;

        $imagine = $this->createImagineMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image))
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbMergedConfig)
            ->will($this->returnArgument(0))
        ;

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->getMockMimeTypeGuesser()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $this->assertInstanceOf(
            'Liip\ImagineBundle\Model\Binary',
            $filterManager->applyFilter($binary, 'thumbnail', $runtimeConfig)
        );
    }

    public function testThrowsIfNoLoadersAddedForFilterOnApply()
    {
        $binary = new Binary('aContent', 'image/png', 'png');

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $this->createImagineMock(),
            $this->getMockMimeTypeGuesser()
        );

        $this->setExpectedException('InvalidArgumentException', 'Could not find filter loader for "thumbnail" filter type');
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

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($expectedFilteredContent))
        ;

        $imagineMock = $this->createImagineMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->with($originalContent)
            ->will($this->returnValue($image))
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0))
        ;

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->getMockMimeTypeGuesser()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, array(
            'filters' => array(
                'thumbnail' => $thumbConfig,
            ),
            'post_processors' => array(),
        ));

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $filteredBinary);
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

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('aFilteredContent'))
        ;

        $imagineMock = $this->createImagineMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image))
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0))
        ;

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->getMockMimeTypeGuesser()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, array(
            'filters' => array(
                'thumbnail' => $thumbConfig,
            ),
            'post_processors' => array(),
        ));

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $filteredBinary);
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

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('aFilteredContent'))
        ;

        $imagineMock = $this->createImagineMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image))
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0))
        ;

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->getMockMimeTypeGuesser()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, array(
            'format' => $expectedFormat,
            'filters' => array(
                'thumbnail' => $thumbConfig,
            ),
            'post_processors' => array(),
        ));

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $filteredBinary);
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

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('aFilteredContent'))
        ;

        $imagineMock = $this->createImagineMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image))
        ;

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess')
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0))
        ;

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

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $filteredBinary);
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

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($expectedContent))
        ;

        $imagineMock = $this->createImagineMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image))
        ;

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($expectedContent)
            ->will($this->returnValue($expectedMimeType))
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0))
        ;

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

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $filteredBinary);
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

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', array('quality' => $expectedQuality))
            ->will($this->returnValue('aFilteredContent'))
        ;

        $imagineMock = $this->createImagineMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image))
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0))
        ;

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->getMockMimeTypeGuesser()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, array(
            'quality' => $expectedQuality,
            'filters' => array(
                'thumbnail' => $thumbConfig,
            ),
            'post_processors' => array(),
        ));

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $filteredBinary);
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

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', array('quality' => $expectedQuality))
            ->will($this->returnValue('aFilteredContent'))
        ;

        $imagineMock = $this->createImagineMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image))
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0))
        ;

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->getMockMimeTypeGuesser()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, array(
            'filters' => array(
                'thumbnail' => $thumbConfig,
            ),
            'post_processors' => array(),
        ));

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $filteredBinary);
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
            )))
        ;

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($originalContent))
        ;

        $imagineMock = $this->createImagineMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->with($originalContent)
            ->will($this->returnValue($image))
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0))
        ;

        $processedBinary = new Binary($expectedPostProcessedContent, 'image/png', 'png');

        $postProcessor = $this->getPostProcessorMock();
        $postProcessor
            ->expects($this->once())
            ->method('process')
            ->with($binary)
            ->will($this->returnValue($processedBinary));

        $filterManager = new FilterManager(
            $config,
            $imagineMock,
            $this->getMockMimeTypeGuesser()
        );
        $filterManager->addLoader('thumbnail', $loader);
        $filterManager->addPostProcessor('foo', $postProcessor);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');
        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $filteredBinary);
        $this->assertEquals($expectedPostProcessedContent, $filteredBinary->getContent());
    }

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
            )))
        ;

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($originalContent))
        ;

        $imagineMock = $this->createImagineMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->with($originalContent)
            ->will($this->returnValue($image))
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0))
        ;

        $filterManager = new FilterManager(
            $config,
            $imagineMock,
            $this->getMockMimeTypeGuesser()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $this->setExpectedException('InvalidArgumentException', 'Could not find post processor "foo"');

        $filterManager->applyFilter($binary, 'thumbnail');
    }

    public function testApplyPostProcessorsWhenNotDefined()
    {
        $binary = $this->getMock('Liip\ImagineBundle\Binary\BinaryInterface');
        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $this->createImagineMock(),
            $this->getMockMimeTypeGuesser()
        );

        $this->assertSame($binary, $filterManager->applyPostProcessors($binary, array()));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoaderInterface
     */
    protected function getMockLoader()
    {
        return $this->getMock('Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Liip\ImagineBundle\Binary\MimeTypeGuesserInterface
     */
    protected function getMockMimeTypeGuesser()
    {
        return $this->getMock('Liip\ImagineBundle\Binary\MimeTypeGuesserInterface');
    }

    protected function getPostProcessorMock()
    {
        return $this->getMock('Liip\ImagineBundle\Imagine\Filter\PostProcessor\PostProcessorInterface');
    }
}
