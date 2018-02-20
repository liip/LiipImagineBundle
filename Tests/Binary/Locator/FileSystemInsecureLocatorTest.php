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

use Liip\ImagineBundle\Binary\Locator\FileSystemInsecureLocator;
use Liip\ImagineBundle\Binary\Locator\LocatorInterface;

/**
 * @covers \Liip\ImagineBundle\Binary\Locator\FileSystemInsecureLocator
 */
class FileSystemInsecureLocatorTest extends AbstractFileSystemLocatorTest
{
    /**
     * @param string|string[] $paths
     *
     * @return LocatorInterface
     */
    protected function getFileSystemLocator($paths)
    {
        $locator = new FileSystemInsecureLocator();
        $locator->setOptions(array('roots' => (array) $paths));

        return $locator;
    }

    public function testLoadsOnSymbolicLinks()
    {
        $loader = $this->getFileSystemLocator($root = realpath(__DIR__.'/../../Fixtures/FileSystemLocator/root-02'));
        $this->assertStringStartsWith(realpath($root), $loader->locate('root-01/file.ext'));
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException
     * @expectedExceptionMessage Source image not resolvable
     */
    public function testThrowsIfPathHasDoublePeriodBackStep()
    {
        $this->getFileSystemLocator(realpath(__DIR__.'/../../Fixtures/FileSystemLocator/root-02'))->locate('/../root-01/file.ext');
    }

    public function testRootPlaceholders()
    {
        $root01 = realpath(__DIR__.'/../../Fixtures/FileSystemLocator/root-01');
        $root02 = realpath(__DIR__.'/../../Fixtures/FileSystemLocator/root-02');

        $loader = $this->getFileSystemLocator(array(
            'root-01' => $root01,
            'root-02' => $root02,
        ));

        $this->assertStringStartsWith($root01, $loader->locate('@root-01:file.ext'));
        $this->assertStringStartsWith($root02, $loader->locate('@root-02:root-01/file.ext'));
    }

    /**
     * @return array[]
     */
    public static function provideLoadCases()
    {
        $fileName = pathinfo(__FILE__, PATHINFO_BASENAME);

        return array(
            array(__DIR__, $fileName),
            array(__DIR__.'/', $fileName),
            array(__DIR__, '/'.$fileName),
            array(__DIR__.'/../../Binary/Locator', '/'.$fileName),
            array(realpath(__DIR__.'/..'), 'Locator/'.$fileName),
        );
    }

    /**
     * @return array[]
     */
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
}
