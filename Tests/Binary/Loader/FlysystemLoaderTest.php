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
use Liip\ImagineBundle\Binary\Loader\FlysystemLoader;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;

/**
 * @requires PHP 5.4
 *
 * @covers \Liip\ImagineBundle\Binary\Loader\FlysystemLoader
 */
class FlysystemLoaderTest extends AbstractTest
{
    private $flyFilesystem;

    public function setUp()
    {
        parent::setUp();

        if (!class_exists(Filesystem::class)) {
            $this->markTestSkipped('Requires the league/flysystem package.');
        }

        $this->flyFilesystem = new Filesystem(new Local($this->fixturesPath));
    }

    public function testConstruction()
    {
        return new FlysystemLoader(ExtensionGuesser::getInstance(), $this->flyFilesystem);
    }

    /**
     * @depends testConstruction
     */
    public function testShouldImplementLoaderInterface(LoaderInterface $loader)
    {
        $this->assertInstanceOf(LoaderInterface::class, $loader);
    }

    /**
     * @depends testConstruction
     */
    public function testReturnImageContentOnFind(LoaderInterface $loader)
    {
        $this->assertSame(
            file_get_contents($this->fixturesPath.'/assets/cats.jpeg'),
            $loader->find('assets/cats.jpeg')->getContent()
        );
    }

    /**
     * @depends testConstruction
     *
     * @expectedException \Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException
     * @expectedExceptionMessageRegExp {Source image .+ not found}
     */
    public function testThrowsIfInvalidPathGivenOnFind(LoaderInterface $loader)
    {
        $loader->find('invalid.jpeg');
    }
}
