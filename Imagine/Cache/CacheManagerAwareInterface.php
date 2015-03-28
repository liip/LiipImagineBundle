<?php

namespace Liip\ImagineBundle\Imagine\Cache;

interface CacheManagerAwareInterface
{
    /**
     * @param CacheManager $cacheManager
     */
    public function setCacheManager(CacheManager $cacheManager);
}
