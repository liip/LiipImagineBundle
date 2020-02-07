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
use Liip\ImagineBundle\Templating\Helper\FilterHelper;
use Liip\ImagineBundle\Tests\AbstractTest;

abstract class AbstractFilterTest extends AbstractTest
{
    public function testCanBeConstructed(): void
    {
        $this->createTemplatingMock();
    }

    public function testInvokeGetNameMethod(): void
    {
        $this->assertSame('liip_imagine', $this->createTemplatingMock()->getName());
    }

    public function testInvokeFilterMethod(): void
    {
        $expectedFilter = 'thumbnail';
        $expectedInputPath = 'thePathToTheImage';
        $expectedCachePath = 'thePathToTheCachedImage';

        $manager = $this->createCacheManagerMock();
        $manager
            ->expects($this->once())
            ->method('getBrowserPath')
            ->with($expectedInputPath, $expectedFilter)
            ->willReturn($expectedCachePath);

        $this->assertSame($expectedCachePath, $this->createTemplatingMock($manager)->filter($expectedInputPath, $expectedFilter));
    }

    /**
     * @return FilterRuntime|FilterHelper
     */
    abstract protected function createTemplatingMock(CacheManager $manager = null);
}
