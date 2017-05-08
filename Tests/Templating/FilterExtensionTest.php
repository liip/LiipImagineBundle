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

/**
 * @covers \Liip\ImagineBundle\Templating\FilterTrait
 * @covers \Liip\ImagineBundle\Templating\FilterExtension
 */
class FilterExtensionTest extends AbstractFilterTest
{
    public function testAddsFilterMethodToFiltersList()
    {
        $this->assertCount(1, $this->createTemplatingMock()->getFilters());
    }

    public function testInstanceOfTwigFilter()
    {
        $this->assertInstanceOf(\Twig_Extension::class, $this->createTemplatingMock());
    }

    /**
     * @param CacheManager|null $manager
     *
     * @return FilterExtension
     */
    protected function createTemplatingMock(CacheManager $manager = null)
    {
        return new FilterExtension($manager ?: $this->createCacheManagerMock());
    }
}
