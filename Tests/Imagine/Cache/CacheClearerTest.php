<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache;

use Liip\ImagineBundle\Imagine\Cache\CacheClearer;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\CacheClearer
 */
class CacheClearerTest extends AbstractTest
{
    public function testClearIgnoresCacheDirectory()
    {
        $cacheManager = $this->getMockCacheManager();
        $cacheManager
            ->expects($this->once())
            ->method('clearResolversCache')
            ->with('/media/cache')
        ;

        $cacheClearer = new CacheClearer($cacheManager, '/media/cache');
        $cacheClearer->clear($this->tempDir.'/cache');
    }
}
