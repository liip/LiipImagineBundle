<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Utility\Path\PathResolverInterface;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractWebPathResolver implements ResolverInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;
    
    /**
     * @var PathResolverInterface
     */
    protected $pathResolver;
    
    /**
     * @param Filesystem     $filesystem
     * @param PathResolverInterface $pathResolver
     */
    public function __construct(
        Filesystem $filesystem,
        PathResolverInterface $pathResolver
    ) {
        $this->filesystem = $filesystem;
        $this->pathResolver = $pathResolver;
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
        return is_file($this->pathResolver->getFilePath($path, $filter));
    }
    
    /**
     * {@inheritdoc}
     */
    public function store(BinaryInterface $binary, $path, $filter)
    {
        $this->filesystem->dumpFile(
            $this->pathResolver->getFilePath($path, $filter),
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
                $filtersCacheDir[] = $this->pathResolver->getCacheRoot().'/'.$filter;
            }
            
            $this->filesystem->remove($filtersCacheDir);
            
            return;
        }
        
        foreach ($paths as $path) {
            foreach ($filters as $filter) {
                $this->filesystem->remove($this->pathResolver->getFilePath($path, $filter));
            }
        }
    }
    
    /**
     * @return PathResolverInterface
     */
    protected function getPathResolver()
    {
        return $this->pathResolver;
    }
}
