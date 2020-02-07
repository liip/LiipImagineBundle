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
use Liip\ImagineBundle\Templating\FilterRuntime;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * @covers \Liip\ImagineBundle\Templating\FilterTrait
 * @covers \Liip\ImagineBundle\Templating\FilterRuntime
 */
class FilterRuntimeTest extends AbstractFilterTest
{
    public function testInstanceOfTwigFilter(): void
    {
        $this->assertInstanceOf(RuntimeExtensionInterface::class, $this->createTemplatingMock());
    }

    protected function createTemplatingMock(CacheManager $manager = null): FilterRuntime
    {
        if (!class_exists(RuntimeExtensionInterface::class)) {
            $this->markTestSkipped('Requires the twig/twig package.');
        }

        $mock = new FilterRuntime($manager ?: $this->createCacheManagerMock());

        $this->assertInstanceOf(FilterRuntime::class, $mock);

        return $mock;
    }
}
