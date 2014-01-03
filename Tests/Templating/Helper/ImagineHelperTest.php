<?php

namespace Liip\ImagineBundle\Tests\Templating\Helper;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Templating\Helper\ImagineHelper;

/**
 * @covers Liip\ImagineBundle\Templating\Helper\ImagineHelper
 */
class ImagineHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testSubClassOfHelper()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Templating\Helper\ImagineHelper');

        $this->assertTrue($rc->isSubclassOf('Symfony\Component\Templating\Helper\Helper'));
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
            ->will($this->returnValue($expectedCachePath))
        ;

        $helper = new ImagineHelper($cacheManager);

        $this->assertEquals($expectedCachePath, $helper->filter($expectedPath, $expectedFilter));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CacheManager
     */
    protected function createCacheManagerMock()
    {
        return $this->getMock('Liip\ImagineBundle\Imagine\Cache\CacheManager', array(), array(), '', false);
    }
}
