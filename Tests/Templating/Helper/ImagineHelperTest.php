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

use Liip\ImagineBundle\Templating\Helper\ImagineHelper;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Templating\Helper\ImagineHelper
 */
class ImagineHelperTest extends AbstractTest
{
    public function testSubClassOfHelper()
    {
        $rc = new \ReflectionClass('\Liip\ImagineBundle\Templating\Helper\ImagineHelper');

        $this->assertTrue($rc->isSubclassOf('\Symfony\Component\Templating\Helper\Helper'));
    }

    public function testCouldBeConstructedWithCacheManagerAsArgument()
    {
        new ImagineHelper($this->createCacheManagerMock());
    }

    public function testAllowGetName()
    {
        $helper = new ImagineHelper($this->createCacheManagerMock());

        $this->assertEquals('liip_imagine', $helper->getName());
    }

    public function testProxyCallToCacheManagerOnFilter()
    {
        $expectedPath = 'thePathToTheImage';
        $expectedFilter = 'thumbnail';
        $expectedCachePath = 'thePathToTheCachedImage';

        $cacheManager = $this->createCacheManagerMock();
        $cacheManager
            ->expects($this->once())
            ->method('getBrowserPath')
            ->with($expectedPath, $expectedFilter)
            ->will($this->returnValue($expectedCachePath));

        $helper = new ImagineHelper($cacheManager);

        $this->assertEquals($expectedCachePath, $helper->filter($expectedPath, $expectedFilter));
    }
}
