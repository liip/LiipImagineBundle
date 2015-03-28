<?php

namespace Liip\ImagineBundle\Tests\Templating\Helper;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Templating\ImagineExtension;

/**
 * @covers Liip\ImagineBundle\Templating\ImagineExtension
 */
class ImagineExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testSubClassOfHelper()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Templating\ImagineExtension');

        $this->assertTrue($rc->isSubclassOf('Twig_Extension'));
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
            ->will($this->returnValue($expectedCachePath))
        ;

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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CacheManager
     */
    protected function createCacheManagerMock()
    {
        return $this->getMock('Liip\ImagineBundle\Imagine\Cache\CacheManager', array(), array(), '', false);
    }
}
