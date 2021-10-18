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
use Liip\ImagineBundle\Templating\FilterExtension;
use Twig\Extension\AbstractExtension;

/**
 * @covers \Liip\ImagineBundle\Templating\FilterTrait
 * @covers \Liip\ImagineBundle\Templating\FilterExtension
 * @group legacy
 */
class FilterExtensionTest extends AbstractFilterTest
{
    public function testAddsFilterMethodToFiltersList(): void
    {
        $this->assertCount(2, $this->createTemplatingMock()->getFilters());
    }

    public function testInstanceOfTwigFilter(): void
    {
        $this->assertInstanceOf(AbstractExtension::class, $this->createTemplatingMock());
    }

    protected function createTemplatingMock(CacheManager $manager = null): FilterExtension
    {
        if (!class_exists(AbstractExtension::class)) {
            $this->markTestSkipped('Requires the twig/twig package.');
        }

        $mock = new FilterExtension($manager ?: $this->createCacheManagerMock());

        $this->assertInstanceOf(FilterExtension::class, $mock);

        return $mock;
    }
}
