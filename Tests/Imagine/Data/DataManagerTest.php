<?php

namespace Liip\ImagineBundle\Tests\Imagine\Data;

use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers Liip\ImagineBundle\Imagine\Data\DataManager
 */
class DataManagerTest extends AbstractTest
{
    public function testUseDefaultLoaderUsedIfNoneSet()
    {
        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'data_loader' => null,
            )))
        ;

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->will($this->returnValue('image/png'))
        ;

        $dataManager = new DataManager($mimeTypeGuesser, $this->getMockExtensionGuesser(), $config, 'default');
        $dataManager->addLoader('default', $loader);

        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testUseLoaderRegisteredForFilterOnFind()
    {
        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            )))
        ;

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->will($this->returnValue('image/png'))
        ;

        $dataManager = new DataManager($mimeTypeGuesser, $this->getMockExtensionGuesser(), $config);
        $dataManager->addLoader('the_loader', $loader);

        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowsIfMimeTypeWasNotGuessedOnFind()
    {
        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            )))
        ;

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->will($this->returnValue(null))
        ;

        $dataManager = new DataManager($mimeTypeGuesser, $this->getMockExtensionGuesser(), $config);
        $dataManager->addLoader('the_loader', $loader);

        $this->setExpectedException('LogicException', 'The mime type of image cats.jpeg was not guessed.');
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowsIfMimeTypeNotImageOneOnFind()
    {
        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->will($this->returnValue('content'))
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            )))
        ;

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->will($this->returnValue('text/plain'))
        ;

        $dataManager = new DataManager($mimeTypeGuesser, $this->getMockExtensionGuesser(), $config);
        $dataManager->addLoader('the_loader', $loader);

        $this->setExpectedException('LogicException', 'The mime type of image cats.jpeg must be image/xxx got text/plain.');
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowsIfLoaderReturnBinaryWithEmtptyMimeTypeOnFind()
    {
        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->will($this->returnValue(new Binary('content', null)))
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            )))
        ;

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess')
        ;

        $dataManager = new DataManager($mimeTypeGuesser, $this->getMockExtensionGuesser(), $config);
        $dataManager->addLoader('the_loader', $loader);

        $this->setExpectedException('LogicException', 'The mime type of image cats.jpeg was not guessed.');
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowsIfLoaderReturnBinaryWithMimeTypeNotImageOneOnFind()
    {
        $binary = new Binary('content', 'text/plain');

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->will($this->returnValue($binary))
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            )))
        ;

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess')
        ;

        $dataManager = new DataManager($mimeTypeGuesser, $this->getMockExtensionGuesser(), $config);
        $dataManager->addLoader('the_loader', $loader);

        $this->setExpectedException('LogicException', 'The mime type of image cats.jpeg must be image/xxx got text/plain.');
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

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
            )))
        ;

        $dataManager = new DataManager($this->getMockMimeTypeGuesser(), $this->getMockExtensionGuesser(), $config);

        $this->setExpectedException('InvalidArgumentException', 'Could not find data loader "" for "thumbnail" filter type');
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testShouldReturnBinaryWithLoaderContentAndGuessedMimeTypeOnFind()
    {
        $expectedContent = 'theImageBinaryContent';
        $expectedMimeType = 'image/png';

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue($expectedContent))
        ;

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($expectedContent)
            ->will($this->returnValue($expectedMimeType))
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'data_loader' => null,
            )))
        ;

        $dataManager = new DataManager($mimeTypeGuesser, $this->getMockExtensionGuesser(), $config, 'default');
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

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue($content))
        ;

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($content)
            ->will($this->returnValue($mimeType))
        ;

        $extensionGuesser = $this->getMockExtensionGuesser();
        $extensionGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($mimeType)
            ->will($this->returnValue($expectedFormat))
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'data_loader' => null,
            )))
        ;

        $dataManager = new DataManager($mimeTypeGuesser, $extensionGuesser, $config, 'default');
        $dataManager->addLoader('default', $loader);

        $binary = $dataManager->find('thumbnail', 'cats.jpeg');

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $binary);
        $this->assertEquals($expectedFormat, $binary->getFormat());
    }

    public function testUseDefaultGlobalImageUsedIfImageNotFound()
    {
        $loader = $this->getMockLoader();

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'default_image' => null,
            )))
        ;

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess')
        ;

        $defaultGlobalImage = 'cats.jpeg';
        $dataManager = new DataManager($mimeTypeGuesser, $this->getMockExtensionGuesser(), $config, 'default', 'cats.jpeg');
        $dataManager->addLoader('default', $loader);

        $defaultImage = $dataManager->getDefaultImageUrl('thumbnail');
        $this->assertEquals($defaultImage, $defaultGlobalImage);
    }

    public function testUseDefaultFilterImageUsedIfImageNotFound()
    {
        $loader = $this->getMockLoader();

        $defaultFilterImage = 'cats.jpeg';

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'default_image' => $defaultFilterImage,
            )))
        ;

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess')
        ;

        $dataManager = new DataManager($mimeTypeGuesser, $this->getMockExtensionGuesser(), $config, 'default', null);
        $dataManager->addLoader('default', $loader);

        $defaultImage = $dataManager->getDefaultImageUrl('thumbnail');
        $this->assertEquals($defaultImage, $defaultFilterImage);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoaderInterface
     */
    protected function getMockLoader()
    {
        return $this->getMock('Liip\ImagineBundle\Binary\Loader\LoaderInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Liip\ImagineBundle\Binary\MimeTypeGuesserInterface
     */
    protected function getMockMimeTypeGuesser()
    {
        return $this->getMock('Liip\ImagineBundle\Binary\MimeTypeGuesserInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface
     */
    protected function getMockExtensionGuesser()
    {
        return $this->getMock('Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface');
    }
}
