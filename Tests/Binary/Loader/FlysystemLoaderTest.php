<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Binary\Loader;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Liip\ImagineBundle\Binary\Loader\FlysystemLoader;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\Mime\MimeTypes;

/**
 * @requires PHP 5.4
 *
 * @covers \Liip\ImagineBundle\Binary\Loader\FlysystemLoader
 */
class FlysystemLoaderTest extends AbstractTest
{
    private $flyFilesystem;

    protected function setUp(): void
    {
        parent::setUp();

        if (!interface_exists(FilesystemInterface::class)) {
            $this->markTestSkipped('Requires the league/flysystem:^1.0 package.');
        }

        $this->flyFilesystem = new Filesystem(new Local($this->fixturesPath));
    }

    public function getFlysystemLoader(): FlysystemLoader
    {
        $extensionGuesser = class_exists(MimeTypes::class) ? MimeTypes::getDefault() : ExtensionGuesser::getInstance();

        return new FlysystemLoader($extensionGuesser, $this->flyFilesystem);
    }

    public function testShouldImplementLoaderInterface(): void
    {
        $this->assertInstanceOf(LoaderInterface::class, $this->getFlysystemLoader());
    }

    public function testThrowsIfConstructedWithWrongTypeArguments(): void
    {
        $this->expectException(\Liip\ImagineBundle\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('$extensionGuesser must be an instance of Symfony\Component\Mime\MimeTypesInterface or Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface');

        new FlysystemLoader(
            'foo',
            $this->flyFilesystem
        );
    }

    public function testReturnImageContentOnFind(): void
    {
        $loader = $this->getFlysystemLoader();

        $this->assertStringEqualsFile(
            $this->fixturesPath.'/assets/cats.jpeg', $loader->find('assets/cats.jpeg')->getContent()
        );
    }

    public function testThrowsIfInvalidPathGivenOnFind(): void
    {
        $this->expectException(\Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException::class);
        $this->expectExceptionMessageRegExp('{Source image .+ not found}');

        $loader = $this->getFlysystemLoader();

        $loader->find('invalid.jpeg');
    }
}
