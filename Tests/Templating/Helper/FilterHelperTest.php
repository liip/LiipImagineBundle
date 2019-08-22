<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Templating\Helper;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Templating\Helper\FilterHelper;
use Liip\ImagineBundle\Tests\Templating\AbstractFilterTest;
use Symfony\Component\Templating\Helper\Helper;

/**
 * @covers \Liip\ImagineBundle\Templating\FilterTrait
 * @covers \Liip\ImagineBundle\Templating\Helper\FilterHelper
 *
 * @group legacy
 */
class FilterHelperTest extends AbstractFilterTest
{
    public function testInstanceOfSymfonyHelper()
    {
        $this->assertInstanceOf(Helper::class, $this->createTemplatingMock());
    }

    /**
     * @param CacheManager|null $manager
     *
     * @return FilterHelper
     */
    protected function createTemplatingMock(CacheManager $manager = null)
    {
        $mock = new FilterHelper($manager ?: $this->createCacheManagerMock());

        $this->assertInstanceOf(FilterHelper::class, $mock);

        return $mock;
    }
}
