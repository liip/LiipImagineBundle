<?php

namespace Liip\ImagineBundle\Tests\Imagine\Data;

use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Data\Loader\LoaderInterface;
use Liip\ImagineBundle\Imagine\MimeTypeGuesserInterface;
use Liip\ImagineBundle\Imagine\RawImage;
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

        $config = $this->getMockFilterConfiguration();
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

        $dataManager = new DataManager($mimeTypeGuesser, $config, 'default');
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

        $config = $this->getMockFilterConfiguration();
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

        $dataManager = new DataManager($mimeTypeGuesser, $config);
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

        $config = $this->getMockFilterConfiguration();
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

        $dataManager = new DataManager($mimeTypeGuesser, $config);
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

        $config = $this->getMockFilterConfiguration();
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

        $dataManager = new DataManager($mimeTypeGuesser, $config);
        $dataManager->addLoader('the_loader', $loader);

        $this->setExpectedException('LogicException', 'The mime type of image cats.jpeg must be image/xxx got text/plain.');
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowsIfLoaderReturnRawImageWithEmtptyMimeTypeOnFind()
    {
        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->will($this->returnValue(new RawImage('content', null)))
        ;

        $config = $this->getMockFilterConfiguration();
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

        $dataManager = new DataManager($mimeTypeGuesser, $config);
        $dataManager->addLoader('the_loader', $loader);

        $this->setExpectedException('LogicException', 'The mime type of image cats.jpeg was not guessed.');
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowsIfLoaderReturnRawImageWithMimeTypeNotImageOneOnFind()
    {
        $rawImage = new RawImage('content', 'text/plain');

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->will($this->returnValue($rawImage))
        ;

        $config = $this->getMockFilterConfiguration();
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

        $dataManager = new DataManager($mimeTypeGuesser, $config);
        $dataManager->addLoader('the_loader', $loader);

        $this->setExpectedException('LogicException', 'The mime type of image cats.jpeg must be image/xxx got text/plain.');
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowIfLoaderNotRegisteredForGivenFilterOnFind()
    {
        $config = $this->getMockFilterConfiguration();
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

        $dataManager = new DataManager($this->getMockMimeTypeGuesser(), $config);

        $this->setExpectedException('InvalidArgumentException', 'Could not find data loader for "thumbnail" filter type');
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testShouldReturnRawImageWithLoaderContentAndGuessedMimeTypeOnFind()
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

        $config = $this->getMockFilterConfiguration();
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

        $dataManager = new DataManager($mimeTypeGuesser, $config, 'default');
        $dataManager->addLoader('default', $loader);

        $rawImage = $dataManager->find('thumbnail', 'cats.jpeg');

        $this->assertInstanceOf('Liip\ImagineBundle\Imagine\RawImage', $rawImage);
        $this->assertEquals($expectedContent, $rawImage->getContent());
        $this->assertEquals($expectedMimeType, $rawImage->getMimeType());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoaderInterface
     */
    protected function getMockLoader()
    {
        return $this->getMock('Liip\ImagineBundle\Imagine\Data\Loader\LoaderInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MimeTypeGuesserInterface
     */
    protected function getMockMimeTypeGuesser()
    {
        return $this->getMock('Liip\ImagineBundle\Imagine\MimeTypeGuesserInterface');
    }
}
