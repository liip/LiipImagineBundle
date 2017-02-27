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
    abstract protected function getLocator($paths);

    public function testShouldImplementLocatorInterface()
    {
        $this->assertInstanceOf('\Liip\ImagineBundle\Binary\Locator\LocatorInterface', new FileSystemLocator());
    }

    public function testThrowExceptionIfRootPathIsEmpty()
    {
        $this->setExpectedException(
            'Liip\ImagineBundle\Exception\InvalidArgumentException',
            'Root image path not resolvable'
        );

        $locator = $this->getLocator('');
    }

    public function testThrowExceptionIfRootPathDoesNotExist()
    {
        $this->setExpectedException(
            'Liip\ImagineBundle\Exception\InvalidArgumentException',
            'Root image path not resolvable'
        );

        $this->getLocator('/a/bad/root/path');
    }

    public function testThrowExceptionIfFileNotExist()
    {
        $this->setExpectedException(
            'Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException',
            'Source image not resolvable'
        );

        $locator = $this->getLocator(__DIR__);
        $locator->locate('fileNotExist');
    }

    /**
     * @dataProvider provideLoadCases
     */
    public function testLoad($rootDir, $path)
    {
        $this->assertStringStartsWith(realpath($rootDir.'/../'), $this->getLocator($rootDir)->locate($path));
    }

    /**
     * @dataProvider provideMultipleRootLoadCases
     */
    public function testMultipleRootLoadCases($rootDirs, $path)
    {
        $this->assertNotNull($this->getLocator($rootDirs)->locate($path));
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid options provided to
     */
    public function testThrowsExceptionOnInvalidOptions()
    {
        $locator = $this->getLocator(__DIR__);
        $locator->setOptions(array('foo' => 'bar'));
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException
     * @expectedExceptionMessage Invalid root placeholder "invalid-placeholder" for path
     */
    public function testThrowsExceptionOnInvalidNamedLoadCase()
    {
        $loader = $this->getLocator(__DIR__);
        $loader->locate('@invalid-placeholder:file.ext');
    }
}
