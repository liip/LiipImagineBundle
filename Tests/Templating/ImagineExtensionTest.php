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

use Liip\ImagineBundle\Templating\ImagineExtension;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Templating\ImagineExtension
 */
class ImagineExtensionTest extends AbstractTest
{
    public function testSubClassOfHelper()
    {
        $rc = new \ReflectionClass('\Liip\ImagineBundle\Templating\ImagineExtension');

        $this->assertTrue($rc->isSubclassOf('\Twig_Extension'));
    }

    public function testCouldBeConstructedWithCacheManagerAsArgument()
    {
        new ImagineExtension($this->createCacheManagerMock());
    }

    public function testAllowGetName()
    {
        $extension = new ImagineExtension($this->createCacheManagerMock());

        $this->assertEquals('liip_imagine', $extension->getName());
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

        $extension = new ImagineExtension($cacheManager);

        $this->assertEquals($expectedCachePath, $extension->filter($expectedPath, $expectedFilter));
    }

    public function testAddsFilterMethodToFiltersList()
    {
        $extension = new ImagineExtension($this->createCacheManagerMock());

        $filters = $extension->getFilters();

        $this->assertInternalType('array', $filters);
        $this->assertCount(1, $filters);
    }

    public function testStripsQueryStringFromPathAndAppendToFinalUrl()
    {
        $cacheManager = $this->createCacheManagerMock();

        $cacheManager
            ->method('getBrowserPath')
            ->will($this->returnValue('/resolved/abc.png'));

        $extension = new ImagineExtension($cacheManager);

        $this->assertEquals('/resolved/abc.png?v=123', $extension->filter('abc.png?v=123', 'foo'));
    }

    public function testAppendsQueryStringToExistingQueryStringInFinalUrl()
    {
        $cacheManager = $this->createCacheManagerMock();

        $cacheManager
            ->method('getBrowserPath')
            ->will($this->returnValue('/resolved/abc.png?foo=bar'));

        $extension = new ImagineExtension($cacheManager);

        $this->assertEquals('/resolved/abc.png?foo=bar&v=123', $extension->filter('abc.png?v=123', 'foo'));
    }
}
