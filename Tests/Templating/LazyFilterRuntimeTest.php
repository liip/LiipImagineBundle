<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Templating;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Templating\LazyFilterRuntime;
use Liip\ImagineBundle\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Liip\ImagineBundle\Templating\LazyFilterRuntime
 */
class LazyFilterRuntimeTest extends AbstractTest
{
    /**
     * @var LazyFilterRuntime
     */
    private $runtime;
    /**
     * @var CacheManager&MockObject
     */
    private $manager;

    protected function setUp(): void
    {
        $this->manager = $this->createCacheManagerMock();
        $this->runtime = new LazyFilterRuntime($this->manager);
    }

    public function provideImageNames(): iterable
    {
        yield 'regular' => ['image' => 'cats.jpeg', 'urlimage' => 'cats.jpeg'];
        yield 'whitespace' => ['image' => 'white cat.jpeg', 'urlimage' => 'white%20cat.jpeg'];
        yield 'plus' => ['image' => 'cat+plus.jpeg', 'urlimage' => 'cat%2Bplus.jpeg'];
        yield 'questionmark' => ['image' => 'cat?question.jpeg', 'urlimage' => 'cat%3Fquestion.jpeg'];
        yield 'hash' => ['image' => 'cat#hash.jpeg', 'urlimage' => 'cat%23hash.jpeg'];
    }

    /**
     * @dataProvider provideImageNames
     */
    public function testInvokeFilterMethod($image, $urlimage): void
    {
        $expectedFilter = 'thumbnail';

        $this->manager
            ->expects($this->once())
            ->method('getBrowserPath')
            ->with($image, $expectedFilter)
            ->willReturn($urlimage)
        ;

        $actualPath = $this->runtime->filter($image, $expectedFilter);

        $this->assertSame($urlimage, $actualPath);
    }

    public function testInvokeFilterCacheMethod(): void
    {
        $expectedFilter = 'thumbnail';
        $expectedInputPath = 'thePathToTheImage';
        $expectedCachePath = 'thePathToTheCachedImage';

        $this->manager
            ->expects($this->once())
            ->method('resolve')
            ->with($expectedInputPath, $expectedFilter)
            ->willReturn($expectedCachePath);

        $actualPath = $this->runtime->filterCache($expectedInputPath, $expectedFilter);

        $this->assertSame($expectedCachePath, $actualPath);
    }

    public function testInvokeFilterCacheMethodWithRuntimeConfig(): void
    {
        $expectedFilter = 'thumbnail';
        $expectedInputPath = 'thePathToTheImage';
        $expectedCachePath = 'thePathToTheCachedImage';
        $expectedRuntimeConfig = [
            'thumbnail' => [
                'size' => [100, 100],
            ],
        ];
        $expectedRuntimeConfigPath = 'thePathToTheImageWithRuntimeConfig';

        $this->manager
            ->expects($this->once())
            ->method('getRuntimePath')
            ->with($expectedInputPath, $expectedRuntimeConfig)
            ->willReturn($expectedRuntimeConfigPath)
        ;
        $this->manager
            ->expects($this->once())
            ->method('resolve')
            ->with($expectedRuntimeConfigPath, $expectedFilter)
            ->willReturn($expectedCachePath)
        ;

        $actualPath = $this->runtime->filterCache($expectedInputPath, $expectedFilter, $expectedRuntimeConfig);

        $this->assertSame($expectedCachePath, $actualPath);
    }
}
