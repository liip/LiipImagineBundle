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
    public function testLoadsOnSymbolicLinks(): void
    {
        $loader = $this->getFileSystemLocator($root = realpath(__DIR__.'/../../Fixtures/FileSystemLocator/root-02'));
        $this->assertStringStartsWith(realpath($root), $loader->locate('root-01/file.ext'));
    }

    public function testThrowsIfPathHasDoublePeriodBackStep(): void
    {
        $this->expectException(\Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException::class);
        $this->expectExceptionMessage('Source image not resolvable');

        $this->getFileSystemLocator(realpath(__DIR__.'/../../Fixtures/FileSystemLocator/root-02'))->locate('/../root-01/file.ext');
    }

    public function testRootPlaceholders(): void
    {
        $root01 = realpath(__DIR__.'/../../Fixtures/FileSystemLocator/root-01');
        $root02 = realpath(__DIR__.'/../../Fixtures/FileSystemLocator/root-02');

        $loader = $this->getFileSystemLocator([
            'root-01' => $root01,
            'root-02' => $root02,
        ]);

        $this->assertStringStartsWith($root01, $loader->locate('@root-01:file.ext'));
        $this->assertStringStartsWith($root02, $loader->locate('@root-02:root-01/file.ext'));
    }

    /**
     * @return array[]
     */
    public static function provideLoadCases(): array
    {
        $fileName = pathinfo(__FILE__, PATHINFO_BASENAME);

        return [
            [__DIR__, $fileName],
            [__DIR__.'/', $fileName],
            [__DIR__, '/'.$fileName],
            [__DIR__.'/../../Binary/Locator', '/'.$fileName],
            [realpath(__DIR__.'/..'), 'Locator/'.$fileName],
        ];
    }

    /**
     * @return array[]
     */
    public static function provideMultipleRootLoadCases(): array
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
     */
    protected function getFileSystemLocator($paths): LocatorInterface
    {
        return new FileSystemInsecureLocator((array) $paths);
    }
}
