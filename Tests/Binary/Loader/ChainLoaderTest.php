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

use Liip\ImagineBundle\Binary\Loader\ChainLoader;
use Liip\ImagineBundle\Binary\Loader\FileSystemLoader;
use Liip\ImagineBundle\Binary\Locator\FileSystemLocator;
use Liip\ImagineBundle\Model\FileBinary;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * @covers \Liip\ImagineBundle\Binary\Loader\ChainLoader
 */
class ChainLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruction()
    {
        $this->getChainLoader();
    }

    public function testImplementsLoaderInterface()
    {
        $this->assertInstanceOf('\Liip\ImagineBundle\Binary\Loader\LoaderInterface', $this->getChainLoader());
    }

    /**
     * @return array[]
     */
    public static function provideLoadCases()
    {
        $file = pathinfo(__FILE__, PATHINFO_BASENAME);

        return array(
            array(
                __DIR__,
                $file,
            ),
            array(
                __DIR__.'/',
                $file,
            ),
            array(
                __DIR__, '/'.
                $file,
            ),
            array(
                __DIR__.'/../../Binary/Loader',
                '/'.$file,
            ),
            array(
                realpath(__DIR__.'/..'),
                'Loader/'.$file,
            ),
            array(
                __DIR__.'/../',
                '/Loader/../../Binary/Loader/'.$file,
            ),
        );
    }

    /**
     * @dataProvider provideLoadCases
     *
     * @param string $root
     * @param string $path
     */
    public function testLoad($root, $path)
    {
        $this->assertValidLoaderFindReturn($this->getChainLoader(array($root))->find($path));
    }

    /**
     * @return array[]
     */
    public function provideInvalidPathsData()
    {
        return array(
            array('../Loader/../../Binary/Loader/../../../Resources/config/routing.yaml'),
            array('../../Binary/'),
        );
    }

    /**
     * @dataProvider provideInvalidPathsData
     *
     * @expectedException \Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException
     * @expectedExceptionMessage Source image not resolvable
     */
    public function testThrowsIfFileDoesNotExist($path)
    {
        $this->getChainLoader()->find($path);
    }

    /**
     * @return FileSystemLocator
     */
    private function getFileSystemLocator()
    {
        return new FileSystemLocator();
    }

    /**
     * @param string[] $paths
     *
     * @return ChainLoader
     */
    private function getChainLoader(array $paths = array())
    {
        return new ChainLoader(array(
            'foo' => new FileSystemLoader(
                MimeTypeGuesser::getInstance(),
                ExtensionGuesser::getInstance(),
                $paths ?: array(__DIR__),
                $this->getFileSystemLocator()
            ),
        ));
    }

    /**
     * @param FileBinary|mixed $return
     * @param string|null      $message
     */
    private function assertValidLoaderFindReturn($return, $message = null)
    {
        $this->assertInstanceOf('\Liip\ImagineBundle\Model\FileBinary', $return, $message);
        $this->assertStringStartsWith('text/', $return->getMimeType(), $message);
    }
}
