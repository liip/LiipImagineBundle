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

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Liip\ImagineBundle\Binary\Loader\FlysystemV2Loader;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\Mime\MimeTypes;

/**
 * @requires PHP 7.1
 *
 * @covers \Liip\ImagineBundle\Binary\Loader\FlysystemV2Loader
 */
class FlysystemV2LoaderTest extends AbstractTest
{
    /** @var FilesystemOperator */
    private $flyFilesystem;

    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists(FilesystemOperator::class)) {
            $this->markTestSkipped('Requires the league/flysystem:^2.0 package.');
        }

        $this->flyFilesystem = new Filesystem(new LocalFilesystemAdapter($this->fixturesPath));
    }

    public function testShouldImplementLoaderInterface(): void
    {
        $this->assertInstanceOf(LoaderInterface::class, $this->getFlysystemLoader());
    }

    public function testReturnImageContentOnFind(): void
    {
        $loader = $this->getFlysystemLoader();

        $this->assertStringEqualsFile(
            $this->fixturesPath.'/assets/cats.jpeg',
            $loader->find('assets/cats.jpeg')->getContent()
        );
    }

    public function testThrowsIfInvalidPathGivenOnFind(): void
    {
        $this->expectException(NotLoadableException::class);
        $this->expectExceptionMessageRegExp('{Source image .+ not found}');

        $loader = $this->getFlysystemLoader();

        $loader->find('invalid.jpeg');
    }

    private function getFlysystemLoader(): FlysystemV2Loader
    {
        return new FlysystemV2Loader(MimeTypes::getDefault(), $this->flyFilesystem);
    }
}
