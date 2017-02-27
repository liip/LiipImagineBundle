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

/**
 * @covers \Liip\ImagineBundle\Binary\Locator\FileSystemLocator
 */
class FileSystemLocatorTest extends AbstractFileSystemLocatorTest
{
    /**
     * @return LocatorInterface
     */
    protected function getLocator($paths)
    {
        $locator = new FileSystemLocator();
        $locator->setOptions(array('roots' => (array) $paths));

        return $locator;
    }

    public function testThrowExceptionIfRealPathIsOutsideRootPath1()
    {
        $this->setExpectedException(
            'Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException',
            'Source image invalid'
        );

        $locator = $this->getLocator(__DIR__);
        $locator->locate('../Loader/../../Binary/Loader/../../../Resources/config/routing.xml');
    }

    public function testThrowExceptionIfRealPathIsOutsideRootPath2()
    {
        $this->setExpectedException(
            'Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException',
            'Source image invalid'
        );

        $loader = $this->getLocator(__DIR__);
        $loader->locate('../../Binary/');
    }

    public function testThrowsOnPathSymbolicLinks()
    {
        $this->setExpectedException(
            'Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException',
            'Source image invalid'
        );

        $loader = $this->getLocator($root = realpath(__DIR__.'/../../Fixtures/FileSystemLocator/root-02'));
        $this->assertStringStartsWith(realpath($root), $loader->locate('root-01/file.ext'));
    }

    public static function provideLoadCases()
    {
        $fileName = pathinfo(__FILE__, PATHINFO_BASENAME);

        return array(
            array(__DIR__, $fileName),
            array(__DIR__.'/', $fileName),
            array(__DIR__, '/'.$fileName),
            array(__DIR__.'/../../Binary/Locator', '/'.$fileName),
            array(realpath(__DIR__.'/..'), 'Locator/'.$fileName),
            array(__DIR__.'/../', '/Locator/../../Binary/Locator/'.$fileName),
        );
    }

    public static function provideMultipleRootLoadCases()
    {
        $prepend = array(
            realpath(__DIR__.'/../'),
            realpath(__DIR__.'/../../'),
            realpath(__DIR__.'/../../../'),
        );

        return array_map(function ($params) use ($prepend) {
            return array(array($prepend[mt_rand(0, count($prepend) - 1)], $params[0]), $params[1]);
        }, static::provideLoadCases());
    }

    public function testNamedLoadCases()
    {
        $root01 = realpath(__DIR__.'/../../Fixtures/FileSystemLocator/root-01');
        $root02 = realpath(__DIR__.'/../../Fixtures/FileSystemLocator/root-02');

        $loader = $this->getLocator(array(
            'root-01' => $root01,
            'root-02' => $root02,
        ));

        $this->assertStringStartsWith($root01, $loader->locate('@root-01:file.ext'));
        $this->assertStringStartsWith($root01, $loader->locate('@root-02:root-01/file.ext'));
    }
}
