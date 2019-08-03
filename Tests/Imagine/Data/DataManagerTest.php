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
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => null,
            ]);

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->willReturn('image/png');

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
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            ]);

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->willReturn('image/png');

        $dataManager = new DataManager($mimeTypeGuesser, $this->createExtensionGuesserInterfaceMock(), $config);
        $dataManager->addLoader('the_loader', $loader);

        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowsIfMimeTypeWasNotGuessedOnFind()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The mime type of image cats.jpeg was not guessed');

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
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            ]);

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->willReturn(null);

        $dataManager = new DataManager($mimeTypeGuesser, $this->createExtensionGuesserInterfaceMock(), $config);
        $dataManager->addLoader('the_loader', $loader);
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowsIfMimeTypeNotImageOneOnFind()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The mime type of image cats.jpeg must be image/xxx got text/plain');

        $loader = $this->createBinaryLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->willReturn('content');

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            ]);

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->willReturn('text/plain');

        $dataManager = new DataManager($mimeTypeGuesser, $this->createExtensionGuesserInterfaceMock(), $config);
        $dataManager->addLoader('the_loader', $loader);
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowsIfLoaderReturnBinaryWithEmtptyMimeTypeOnFind()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The mime type of image cats.jpeg was not guessed');

        $loader = $this->createBinaryLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->willReturn(new Binary('content', null));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            ]);

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $dataManager = new DataManager($mimeTypeGuesser, $this->createExtensionGuesserInterfaceMock(), $config);
        $dataManager->addLoader('the_loader', $loader);
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowsIfLoaderReturnBinaryWithMimeTypeNotImageOneOnFind()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The mime type of image cats.jpeg must be image/xxx got text/plain');

        $binary = new Binary('content', 'text/plain');

        $loader = $this->createBinaryLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->willReturn($binary);

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            ]);

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $dataManager = new DataManager($mimeTypeGuesser, $this->createExtensionGuesserInterfaceMock(), $config);
        $dataManager->addLoader('the_loader', $loader);
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowIfLoaderNotRegisteredForGivenFilterOnFind()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not find data loader "" for "thumbnail" filter type');

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => null,
            ]);

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
            ->willReturn($expectedContent);

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($expectedContent)
            ->willReturn($expectedMimeType);

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => null,
            ]);

        $dataManager = new DataManager($mimeTypeGuesser, $this->createExtensionGuesserInterfaceMock(), $config, 'default');
        $dataManager->addLoader('default', $loader);

        $binary = $dataManager->find('thumbnail', 'cats.jpeg');

        $this->assertInstanceOf(Binary::class, $binary);
        $this->assertSame($expectedContent, $binary->getContent());
        $this->assertSame($expectedMimeType, $binary->getMimeType());
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
            ->willReturn($content);

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($content)
            ->willReturn($mimeType);

        $extensionGuesser = $this->createExtensionGuesserInterfaceMock();
        $extensionGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($mimeType)
            ->willReturn($expectedFormat);

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => null,
            ]);

        $dataManager = new DataManager($mimeTypeGuesser, $extensionGuesser, $config, 'default');
        $dataManager->addLoader('default', $loader);

        $binary = $dataManager->find('thumbnail', 'cats.jpeg');

        $this->assertInstanceOf(Binary::class, $binary);
        $this->assertSame($expectedFormat, $binary->getFormat());
    }

    public function testUseDefaultGlobalImageUsedIfImageNotFound()
    {
        $loader = $this->createBinaryLoaderInterfaceMock();

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'default_image' => null,
            ]);

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $defaultGlobalImage = 'cats.jpeg';
        $dataManager = new DataManager($mimeTypeGuesser, $this->createExtensionGuesserInterfaceMock(), $config, 'default', 'cats.jpeg');
        $dataManager->addLoader('default', $loader);

        $defaultImage = $dataManager->getDefaultImageUrl('thumbnail');
        $this->assertSame($defaultImage, $defaultGlobalImage);
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
            ->willReturn([
                'default_image' => $defaultFilterImage,
            ]);

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $dataManager = new DataManager($mimeTypeGuesser, $this->createExtensionGuesserInterfaceMock(), $config, 'default', null);
        $dataManager->addLoader('default', $loader);

        $defaultImage = $dataManager->getDefaultImageUrl('thumbnail');
        $this->assertSame($defaultImage, $defaultFilterImage);
    }
}
