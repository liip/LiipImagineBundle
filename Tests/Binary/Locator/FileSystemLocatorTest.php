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
    public function testAllowInvalidPaths()
    {
        $locator = new FileSystemLocator(['/does/not/exist/foo', '/does/not/exist/bar', $temp = sys_get_temp_dir()], true);
        $roots = (new \ReflectionObject($locator))->getProperty('roots');
        $roots->setAccessible(true);
        $array = [
            '',
            '',
            realpath($temp),
        ];
        unset($array[0], $array[1]);

        $this->assertSame($array, $roots->getValue($locator));
    }

    public function testThrowsIfPathHasSymbolicLinksPointOutsideRoot()
    {
        $this->expectException(\Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException::class);
        $this->expectExceptionMessage('Source image invalid');

        $this->getFileSystemLocator(realpath(__DIR__.'/../../Fixtures/FileSystemLocator/root-02'))->locate('root-01/file.ext');
    }

    public function testRootPlaceholders()
    {
        $root01 = realpath(__DIR__.'/../../Fixtures/FileSystemLocator/root-01');
        $root02 = realpath(__DIR__.'/../../Fixtures/FileSystemLocator/root-02');

        $loader = $this->getFileSystemLocator([
            'root-01' => $root01,
            'root-02' => $root02,
        ]);

        $this->assertStringStartsWith($root01, $loader->locate('@root-01:file.ext'));
        $this->assertStringStartsWith($root01, $loader->locate('@root-02:root-01/file.ext'));
    }

    /**
     * @return array[]
     */
    public static function provideLoadCases()
    {
        $fileName = pathinfo(__FILE__, PATHINFO_BASENAME);

        return [
            [__DIR__, $fileName],
            [__DIR__.'/', $fileName],
            [__DIR__, '/'.$fileName],
            [__DIR__.'/../../Binary/Locator', '/'.$fileName],
            [realpath(__DIR__.'/..'), 'Locator/'.$fileName],
            [__DIR__.'/../', '/Locator/../../Binary/Locator/'.$fileName],
        ];
    }

    /**
     * @return array[]
     */
    public static function provideMultipleRootLoadCases()
    {
        $prepend = [
            realpath(__DIR__.'/../'),
            realpath(__DIR__.'/../../'),
            realpath(__DIR__.'/../../../'),
        ];

        return array_map(function ($params) use ($prepend) {
            return [[$prepend[mt_rand(0, \count($prepend) - 1)], $params[0]], $params[1]];
        }, static::provideLoadCases());
    }

    /**
     * @param string|string[] $paths
     *
     * @return LocatorInterface
     */
    protected function getFileSystemLocator($paths)
    {
        return new FileSystemLocator((array) $paths);
    }
}
