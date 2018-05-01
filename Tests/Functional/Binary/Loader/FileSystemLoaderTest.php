<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Functional\Binary\Loader;

use Liip\ImagineBundle\Binary\Loader\FileSystemLoader;
use Liip\ImagineBundle\Tests\Functional\AbstractWebTestCase;

/**
 * @covers \Liip\ImagineBundle\Binary\Loader\FileSystemLoader
 */
class FileSystemLoaderTest extends AbstractWebTestCase
{
    public function testMultipleLoadersHaveDifferentLocatorInstances()
    {
        static::createClient();

        $fooLoader = $this->getLoader('foo');
        $barLoader = $this->getLoader('bar');

        $fooLocator = $this->getPrivateProperty($fooLoader, 'locator');
        $barLocator = $this->getPrivateProperty($barLoader, 'locator');

        $this->assertNotSame($fooLocator, $barLocator);

        $fooRoots = $this->getPrivateProperty($fooLocator, 'roots');
        $barRoots = $this->getPrivateProperty($barLocator, 'roots');

        $this->assertNotSame($fooRoots, $barRoots);

        $this->assertStringEndsWith('root-01', $fooRoots[0]);
        $this->assertStringEndsWith('root-02', $barRoots[0]);
    }

    /**
     * @param string $name
     *
     * @return FileSystemLoader|object
     */
    private function getLoader($name)
    {
        return $this->getService(sprintf('liip_imagine.binary.loader.%s', $name));
    }
}
