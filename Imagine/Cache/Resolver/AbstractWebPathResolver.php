<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RequestContext;

abstract class AbstractWebPathResolver implements ResolverInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;
    
    /**
     * @var RequestContext
     */
    protected $requestContext;
    
    /**
     * @var string
     */
    protected $webRoot;
    
    /**
     * @var string
     */
    protected $cachePrefix;
    
    /**
     * @var string
     */
    protected $cacheRoot;
    
    /**
     * @param Filesystem     $filesystem
     * @param RequestContext $requestContext
     * @param string         $webRootDir
     * @param string         $cachePrefix
     */
    public function __construct(
        Filesystem $filesystem,
        RequestContext $requestContext,
        $webRootDir,
        $cachePrefix = 'media/cache'
    ) {
        $this->filesystem = $filesystem;
        $this->requestContext = $requestContext;
        
        $this->webRoot = rtrim(str_replace('//', '/', $webRootDir), '/');
        $this->cachePrefix = ltrim(str_replace('//', '/', $cachePrefix), '/');
        $this->cacheRoot = $this->webRoot.'/'.$this->cachePrefix;
    }
    
    /**
     * Checks whether the given path is stored within this Resolver.
     *
     * @param string $path
     * @param string $filter
     *
     * @return bool
     */
    public function isStored($path, $filter)
    {
        return is_file($this->getFilePath($path, $filter));
    }
    
    /**
     * {@inheritdoc}
     */
    public function store(BinaryInterface $binary, $path, $filter)
    {
        $this->filesystem->dumpFile(
            $this->getFilePath($path, $filter),
            $binary->getContent()
        );
    }
    
    /**
     * {@inheritdoc}
     */
    public function remove(array $paths, array $filters)
    {
        if (empty($paths) && empty($filters)) {
            return;
        }
        
        if (empty($paths)) {
            $filtersCacheDir = [];
            foreach ($filters as $filter) {
                $filtersCacheDir[] = $this->cacheRoot.'/'.$filter;
            }
            
            $this->filesystem->remove($filtersCacheDir);
            
            return;
        }
        
        foreach ($paths as $path) {
            foreach ($filters as $filter) {
                $this->filesystem->remove($this->getFilePath($path, $filter));
            }
        }
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getFilePath($path, $filter)
    {
        return $this->webRoot.'/'.$this->getFileUrl($path, $filter);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getFileUrl($path, $filter)
    {
        // crude way of sanitizing URL scheme ("protocol") part
        $path = str_replace('://', '---', $path);
        
        return $this->cachePrefix.'/'.$filter.'/'.ltrim($path, '/');
    }
}
