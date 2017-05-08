<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Binary\Locator;

use Liip\ImagineBundle\Binary\Locator\FileSystemLocator;
use Liip\ImagineBundle\Binary\Locator\LocatorInterface;

abstract class AbstractFileSystemLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string[]|string $paths
     *
     * @return LocatorInterface
     */
    abstract protected function getFileSystemLocator($paths);

    public function testImplementsLocatorInterface()
    {
        $this->assertInstanceOf('\Liip\ImagineBundle\Binary\Locator\LocatorInterface', new FileSystemLocator());
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\InvalidArgumentException
     * @expectedExceptionMessage Root image path not resolvable
     */
    public function testThrowsIfEmptyRootPath()
    {
        $this->getFileSystemLocator('');
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\InvalidArgumentException
     * @expectedExceptionMessage Root image path not resolvable
     */
    public function testThrowsIfRootPathDoesNotExist()
    {
        $this->getFileSystemLocator('/a/bad/root/path');
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException
     * @expectedExceptionMessage Source image not resolvable
     */
    public function testThrowsIfFileDoesNotExist()
    {
        $this->getFileSystemLocator(__DIR__)->locate('fileNotExist');
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid options provided to
     */
    public function testThrowsIfInvalidOptionProvided()
    {
        $this->getFileSystemLocator(__DIR__)->setOptions(array('foo' => 'bar'));
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException
     * @expectedExceptionMessage Invalid root placeholder "invalid-placeholder" for path
     */
    public function testThrowsIfRootPlaceholderInvalid()
    {
        $this->getFileSystemLocator(__DIR__)->locate('@invalid-placeholder:file.ext');
    }

    /**
     * @return array[]
     */
    public static function provideLoadCases()
    {
        return array();
    }

    /**
     * @dataProvider provideLoadCases
     *
     * @param string $root
     * @param string $path
     */
    public function testLoad($root, $path)
    {
        $this->assertStringStartsWith(realpath($root.'/../'), $this->getFileSystemLocator($root)->locate($path));
    }

    /**
     * @return array[]
     */
    public static function provideMultipleRootLoadCases()
    {
        return array();
    }

    /**
     * @dataProvider provideMultipleRootLoadCases
     *
     * @param string $root
     * @param string $path
     */
    public function testMultipleRootLoadCases($root, $path)
    {
        $this->assertNotNull($this->getFileSystemLocator($root)->locate($path));
    }

    /**
     * @return array[]
     */
    public function provideOutsideRootPathsData()
    {
        return array(
            array('../Loader/../../Binary/Loader/../../../Resources/config/routing.yaml'),
            array('../../Binary/'),
        );
    }

    /**
     * @dataProvider provideOutsideRootPathsData
     *
     * @expectedException \Liip\ImagineBundle\Exception\InvalidArgumentException
     * @expectedExceptionMessage Root image path not resolvable
     */
    public function testThrowsIfRealPathOutsideRootPath($path)
    {
        $this->getFileSystemLocator($path)->locate($path);
    }
}
