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
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var string
     */
    protected $cachePrefix;

    /**
     * Constructor.
     *
     * @param CacheManager $cacheManager
     * @param string $cachePrefix The prefix applied to all cached images.
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
