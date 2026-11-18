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

use Liip\ImagineBundle\Binary\Loader\StreamLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Binary\Loader\StreamLoader<extended>
 */
class StreamLoaderTest extends AbstractTest
{
    public function testThrowsIfInvalidPathGivenOnFind(): void
    {
        $this->expectException(\Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException::class);
        $this->expectExceptionMessageMatches('{Source image file://.+ not found.}');

        $loader = new StreamLoader('file://');
        $loader->find($this->temporaryPath.'/invalid.jpeg');
    }

    public function testReturnImageContentOnFind(): void
    {
        $loader = new StreamLoader('file://');

        $this->assertStringEqualsFile(
            $this->fixturesPath.'/assets/cats.jpeg', $loader->find($this->fixturesPath.'/assets/cats.jpeg')
        );
    }

    public function testReturnImageContentWhenStreamContextProvidedOnFind(): void
    {
        $loader = new StreamLoader('file://', stream_context_create());

        $this->assertStringEqualsFile(
            $this->fixturesPath.'/assets/cats.jpeg', $loader->find($this->fixturesPath.'/assets/cats.jpeg')
        );
    }

    public function testThrowsIfPathContainsTraversal(): void
    {
        $loader = new StreamLoader('file://'.$this->fixturesPath.'/assets/');
        // guard: the file exists and would be loaded without the check
        $this->assertFileExists($this->fixturesPath.'/assets/../assets/cats.jpeg');

        $this->expectException(\Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException::class);
        $this->expectExceptionMessage('must not contain ".." segments');

        $loader->find('../assets/cats.jpeg');
    }

    /**
     * @return string[][]
     */
    public static function provideHostChangingPaths(): array
    {
        return [
            'user info' => ['https://images.example.com', '@attacker.test/image.jpg'],
            'user info with backslash' => ['https://images.example.com', '\\@attacker.test/image.jpg'],
            'longer host name' => ['https://images.example.com', '.attacker.test/image.jpg'],
            'port' => ['https://images.example.com', ':8080/image.jpg'],
            'credentials in prefix' => ['ftp://user:secret@files.example.com', '.attacker.test/image.jpg'],
            'storage name' => ['gaufrette://profile_photos', '_private/image.jpg'],
        ];
    }

    /**
     * The check runs before the stream is opened, so these tests do not access the network.
     *
     * @dataProvider provideHostChangingPaths
     */
    public function testThrowsIfPathChangesHostOfWrapperPrefix(string $wrapperPrefix, string $path): void
    {
        $this->expectException(\Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException::class);
        $this->expectExceptionMessage('changes the host of the stream wrapper prefix');

        (new StreamLoader($wrapperPrefix))->find($path);
    }

    /**
     * @return string[][]
     */
    public static function provideHostKeepingPaths(): array
    {
        return [
            'trailing slash' => ['https://images.example.com/', '@attacker.test/image.jpg'],
            'path starting with slash' => ['https://images.example.com', '/image.jpg'],
            'query string' => ['https://legacy.example.com/image.php?file=', 'user@example.com.jpg'],
            'storage name' => ['gaufrette://profile_photos/', 'image.jpg'],
            'only a scheme' => ['file://', '/srv/images/image.jpg'],
            'local directory' => ['/srv/images/', 'image.jpg'],
        ];
    }

    /**
     * @dataProvider provideHostKeepingPaths
     */
    public function testAcceptsPathKeepingHostOfWrapperPrefix(string $wrapperPrefix, string $path): void
    {
        $loader = new StreamLoader($wrapperPrefix);

        $this->assertFalse($this->getVisibilityRestrictedMethod($loader, 'changesHost')->invoke($loader, $wrapperPrefix.$path));
    }

    public function testThrowsIfInvalidResourceGivenInConstructor(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The given context is no valid resource');

        new StreamLoader('an-invalid-resource-name', true);
    }
}
