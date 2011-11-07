<?php

namespace Liip\ImagineBundle\Imagine\Cache;

interface CacheManagerAwareInterface
{
    /**
     * @param CacheManager $cacheManager
     */
    function setCacheManager(CacheManager $cacheManager);
}
