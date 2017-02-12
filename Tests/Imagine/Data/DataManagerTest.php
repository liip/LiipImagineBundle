<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Imagine\Data;

use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Data\DataManager
 */
class DataManagerTest extends AbstractTest
{
    public function testUseDefaultLoaderUsedIfNoneSet()
    {
        $loader = $this->createBinaryLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg');

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'data_loader' => null,
            )));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->will($this->returnValue('image/png'));

        $dataManager = new DataManager($mimeTypeGuesser, $this->createExtensionGuesserInterfaceMock(), $config, 'default');
        $dataManager->addLoader('default', $loader);

        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testUseLoaderRegisteredForFilterOnFind()
    {
        $loader = $this->createBinaryLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg');

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            )));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->will($this->returnValue('image/png'));

        $dataManager = new DataManager($mimeTypeGuesser, $this->createExtensionGuesserInterfaceMock(), $config);
        $dataManager->addLoader('the_loader', $loader);

        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The mime type of image cats.jpeg was not guessed
     */
    public function testThrowsIfMimeTypeWasNotGuessedOnFind()
    {
        $loader = $this->createBinaryLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg');

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            )));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->will($this->returnValue(null));

        $dataManager = new DataManager($mimeTypeGuesser, $this->createExtensionGuesserInterfaceMock(), $config);
        $dataManager->addLoader('the_loader', $loader);
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The mime type of image cats.jpeg must be image/xxx got text/plain
     */
    public function testThrowsIfMimeTypeNotImageOneOnFind()
    {
        $loader = $this->createBinaryLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->will($this->returnValue('content'));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            )));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->will($this->returnValue('text/plain'));

        $dataManager = new DataManager($mimeTypeGuesser, $this->createExtensionGuesserInterfaceMock(), $config);
        $dataManager->addLoader('the_loader', $loader);
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The mime type of image cats.jpeg was not guessed
     */
    public function testThrowsIfLoaderReturnBinaryWithEmtptyMimeTypeOnFind()
    {
        $loader = $this->createBinaryLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->will($this->returnValue(new Binary('content', null)));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            )));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $dataManager = new DataManager($mimeTypeGuesser, $this->createExtensionGuesserInterfaceMock(), $config);
        $dataManager->addLoader('the_loader', $loader);
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The mime type of image cats.jpeg must be image/xxx got text/plain
     */
    public function testThrowsIfLoaderReturnBinaryWithMimeTypeNotImageOneOnFind()
    {
        $binary = new Binary('content', 'text/plain');

        $loader = $this->createBinaryLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->will($this->returnValue($binary));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            )));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $dataManager = new DataManager($mimeTypeGuesser, $this->createExtensionGuesserInterfaceMock(), $config);
        $dataManager->addLoader('the_loader', $loader);
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Could not find data loader "" for "thumbnail" filter type
     */
    public function testThrowIfLoaderNotRegisteredForGivenFilterOnFind()
    {
        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'data_loader' => null,
            )));

        $dataManager = new DataManager($this->createMimeTypeGuesserInterfaceMock(), $this->createExtensionGuesserInterfaceMock(), $config);
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testShouldReturnBinaryWithLoaderContentAndGuessedMimeTypeOnFind()
    {
        $expectedContent = 'theImageBinaryContent';
        $expectedMimeType = 'image/png';

        $loader = $this->createBinaryLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue($expectedContent));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($expectedContent)
            ->will($this->returnValue($expectedMimeType));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'data_loader' => null,
            )));

        $dataManager = new DataManager($mimeTypeGuesser, $this->createExtensionGuesserInterfaceMock(), $config, 'default');
        $dataManager->addLoader('default', $loader);

        $binary = $dataManager->find('thumbnail', 'cats.jpeg');

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $binary);
        $this->assertEquals($expectedContent, $binary->getContent());
        $this->assertEquals($expectedMimeType, $binary->getMimeType());
    }

    public function testShouldReturnBinaryWithLoaderContentAndGuessedFormatOnFind()
    {
        $content = 'theImageBinaryContent';
        $mimeType = 'image/png';
        $expectedFormat = 'png';

        $loader = $this->createBinaryLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue($content));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($content)
            ->will($this->returnValue($mimeType));

        $extensionGuesser = $this->createExtensionGuesserInterfaceMock();
        $extensionGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($mimeType)
            ->will($this->returnValue($expectedFormat));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'data_loader' => null,
            )));

        $dataManager = new DataManager($mimeTypeGuesser, $extensionGuesser, $config, 'default');
        $dataManager->addLoader('default', $loader);

        $binary = $dataManager->find('thumbnail', 'cats.jpeg');

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $binary);
        $this->assertEquals($expectedFormat, $binary->getFormat());
    }

    public function testUseDefaultGlobalImageUsedIfImageNotFound()
    {
        $loader = $this->createBinaryLoaderInterfaceMock();

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'default_image' => null,
            )));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $defaultGlobalImage = 'cats.jpeg';
        $dataManager = new DataManager($mimeTypeGuesser, $this->createExtensionGuesserInterfaceMock(), $config, 'default', 'cats.jpeg');
        $dataManager->addLoader('default', $loader);

        $defaultImage = $dataManager->getDefaultImageUrl('thumbnail');
        $this->assertEquals($defaultImage, $defaultGlobalImage);
    }

    public function testUseDefaultFilterImageUsedIfImageNotFound()
    {
        $loader = $this->createBinaryLoaderInterfaceMock();

        $defaultFilterImage = 'cats.jpeg';

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'default_image' => $defaultFilterImage,
            )));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $dataManager = new DataManager($mimeTypeGuesser, $this->createExtensionGuesserInterfaceMock(), $config, 'default', null);
        $dataManager->addLoader('default', $loader);

        $defaultImage = $dataManager->getDefaultImageUrl('thumbnail');
        $this->assertEquals($defaultImage, $defaultFilterImage);
    }
}
