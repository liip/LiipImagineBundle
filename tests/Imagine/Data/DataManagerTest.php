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

use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Binary\MimeTypeGuesserInterface;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\Mime\MimeTypesInterface;

/**
 * @covers \Liip\ImagineBundle\Imagine\Data\DataManager
 */
class DataManagerTest extends AbstractTest
{
    public function testUseDefaultLoaderUsedIfNoneSet(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->willReturn('content');

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => null,
            ]);

        $mimeTypeGuesser = $this->createMock(MimeTypeGuesserInterface::class);
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->willReturn('image/png');

        $dataManager = new DataManager($mimeTypeGuesser, $this->createMock(MimeTypesInterface::class), $config, 'default');
        $dataManager->addLoader('default', $loader);

        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testUseLoaderRegisteredForFilterOnFind(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->willReturn('content');

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            ]);

        $mimeTypeGuesser = $this->createMock(MimeTypeGuesserInterface::class);
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->willReturn('image/png');

        $dataManager = new DataManager($mimeTypeGuesser, $this->createMock(MimeTypesInterface::class), $config);
        $dataManager->addLoader('the_loader', $loader);

        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowsIfMimeTypeWasNotGuessedOnFind(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->willReturn('content');

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            ]);

        $mimeTypeGuesser = $this->createMock(MimeTypeGuesserInterface::class);
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->willReturn(null);

        $dataManager = new DataManager($mimeTypeGuesser, $this->createMock(MimeTypesInterface::class), $config);
        $dataManager->addLoader('the_loader', $loader);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The mime type of image cats.jpeg was not guessed');

        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowsIfMimeTypeNotImageOneOnFind(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The mime type of file cats.jpeg must be image/xxx or application/pdf, got text/plain');

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->willReturn('content');

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            ]);

        $mimeTypeGuesser = $this->createMock(MimeTypeGuesserInterface::class);
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->willReturn('text/plain');

        $dataManager = new DataManager($mimeTypeGuesser, $this->createMock(MimeTypesInterface::class), $config);
        $dataManager->addLoader('the_loader', $loader);
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowsIfLoaderReturnBinaryWithEmtptyMimeTypeOnFind(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The mime type of image cats.jpeg was not guessed');

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->willReturn(new Binary('content', null));

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            ]);

        $mimeTypeGuesser = $this->createMock(MimeTypeGuesserInterface::class);
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $dataManager = new DataManager($mimeTypeGuesser, $this->createMock(MimeTypesInterface::class), $config);
        $dataManager->addLoader('the_loader', $loader);
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowsIfLoaderReturnBinaryWithMimeTypeNotImageOneOnFind(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The mime type of file cats.jpeg must be image/xxx or application/pdf, got text/plain');

        $binary = new Binary('content', 'text/plain');

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->willReturn($binary);

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            ]);

        $mimeTypeGuesser = $this->createMock(MimeTypeGuesserInterface::class);
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $dataManager = new DataManager($mimeTypeGuesser, $this->createMock(MimeTypesInterface::class), $config);
        $dataManager->addLoader('the_loader', $loader);
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowIfLoaderNotRegisteredForGivenFilterOnFind(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not find data loader "" for "thumbnail" filter type');

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => null,
            ]);

        $dataManager = new DataManager($this->createMock(MimeTypeGuesserInterface::class), $this->createMock(MimeTypesInterface::class), $config);
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testShouldReturnBinaryWithLoaderContentAndGuessedMimeTypeOnFind(): void
    {
        $expectedContent = 'theImageBinaryContent';
        $expectedMimeType = 'image/png';

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('find')
            ->willReturn($expectedContent);

        $mimeTypeGuesser = $this->createMock(MimeTypeGuesserInterface::class);
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($expectedContent)
            ->willReturn($expectedMimeType);

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => null,
            ]);

        $dataManager = new DataManager($mimeTypeGuesser, $this->createMock(MimeTypesInterface::class), $config, 'default');
        $dataManager->addLoader('default', $loader);

        $binary = $dataManager->find('thumbnail', 'cats.jpeg');

        $this->assertInstanceOf(Binary::class, $binary);
        $this->assertSame($expectedContent, $binary->getContent());
        $this->assertSame($expectedMimeType, $binary->getMimeType());
    }

    public function testShouldReturnBinaryWithLoaderContentAndGuessedFormatOnFind(): void
    {
        $content = 'theImageBinaryContent';
        $mimeType = 'image/png';
        $expectedFormat = 'png';

        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('find')
            ->willReturn($content);

        $mimeTypeGuesser = $this->createMock(MimeTypeGuesserInterface::class);
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($content)
            ->willReturn($mimeType);

        $extensionGuesser = $this->createMock(MimeTypesInterface::class);

        if ($extensionGuesser instanceof MimeTypesInterface) {
            $extensionGuesser
                ->expects($this->once())
                ->method('getExtensions')
                ->with($mimeType)
                ->willReturn([$expectedFormat]);
        } else {
            $extensionGuesser
                ->expects($this->once())
                ->method('guess')
                ->with($mimeType)
                ->willReturn($expectedFormat);
        }

        $config = $this->createMock(FilterConfiguration::class);
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

    public function testUseDefaultGlobalImageUsedIfImageNotFound(): void
    {
        $loader = $this->createMock(LoaderInterface::class);

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'default_image' => null,
            ]);

        $mimeTypeGuesser = $this->createMock(MimeTypeGuesserInterface::class);
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $defaultGlobalImage = 'cats.jpeg';
        $dataManager = new DataManager($mimeTypeGuesser, $this->createMock(MimeTypesInterface::class), $config, 'default', 'cats.jpeg');
        $dataManager->addLoader('default', $loader);

        $defaultImage = $dataManager->getDefaultImageUrl('thumbnail');
        $this->assertSame($defaultImage, $defaultGlobalImage);
    }

    public function testUseDefaultFilterImageUsedIfImageNotFound(): void
    {
        $loader = $this->createMock(LoaderInterface::class);

        $defaultFilterImage = 'cats.jpeg';

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'default_image' => $defaultFilterImage,
            ]);

        $mimeTypeGuesser = $this->createMock(MimeTypeGuesserInterface::class);
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $dataManager = new DataManager($mimeTypeGuesser, $this->createMock(MimeTypesInterface::class), $config, 'default', null);
        $dataManager->addLoader('default', $loader);

        $defaultImage = $dataManager->getDefaultImageUrl('thumbnail');
        $this->assertSame($defaultImage, $defaultFilterImage);
    }
}
