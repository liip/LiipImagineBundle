<?php

namespace Liip\ImagineBundle\Imagine\Cache;

use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

/**
 * Clears the Liip Imagine Bundle cache
 * 
 * @author Josiah <josiah@web-dev.com.au>
 */
class CacheClearer implements CacheClearerInterface
{
    /**
     * The Cache Manager
     * 
     * @var CacheManager
     */
    private $cacheManager;
    
    /**
     * The prefix applied to all cached images
     * 
     * @var string
     */
    private $cachePrefix;
    
    /**
     * @param CacheManager $cacheManager
     */
    public function __construct(CacheManager $cacheManager, $cachePrefix)
    {
        $this->cacheManager = $cacheManager;
        $this->cachePrefix = $cachePrefix;
    }
    
    /**
     * (non-PHPdoc)
     * @see Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface::clear()
     */
    public function clear($cacheDir)
    {
        // $cacheDir contains the application cache, which we don't care about
        $this->cacheManager->clearResolversCache($this->cachePrefix);
    }
}
