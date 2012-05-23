<?php
namespace Liip\ImagineBundle\Imagine\Cache;

use Symfony\Component\Finder\Finder;

use Symfony\Component\Filesystem\Filesystem;

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
     * The filesystem utilities
     * 
     * @var Filesystem
     */
    private $filesystem;
    
    /**
     * @param CacheManager $cacheManager
     */
    public function __construct(CacheManager $cacheManager, $cachePrefix, Filesystem $filesystem)
    {
        $this->cacheManager = $cacheManager;
        $this->cachePrefix = $cachePrefix;
        $this->filesystem = $filesystem;
    }
    
    /**
     * (non-PHPdoc)
     * @see Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface::clear()
     */
    public function clear($cacheDir)
    {
        $cachePath = $this->cacheManager->getWebRoot().DIRECTORY_SEPARATOR.$this->cachePrefix;
        
        $this->filesystem->remove(Finder::create()->in($cachePath)->depth(0)->directories());
    }
}
