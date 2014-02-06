<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RequestContext;

class WebPathResolver implements ResolverInterface
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
     * @param Filesystem $filesystem
     * @param RequestContext $requestContext
     * @param string $webRootDir
     * @param string $cachePrefix
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
     * {@inheritDoc}
     */
    public function resolve($path, $filter)
    {
        return sprintf('%s://%s/%s',
            $this->requestContext->getScheme(),
            $this->requestContext->getHost(),
            $this->getFileUrl($path, $filter)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isStored($path, $filter)
    {
        return $this->filesystem->exists($this->getFilePath($path, $filter));
    }

    /**
     * {@inheritDoc}
     */
    public function store(BinaryInterface $binary, $path, $filter)
    {
        $this->filesystem->dumpFile(
            $this->getFilePath($path, $filter),
            $binary->getContent()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function remove(array $paths, array $filters)
    {
        if (empty($paths) && empty($filters)) {
            return;
        }

        if (empty($paths)) {
            $filtersCacheDir = array();
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
     * {@inheritDoc}
     */
    protected function getFilePath($path, $filter)
    {
        return $this->webRoot.'/'.$this->getFileUrl($path, $filter);
    }

    /**
     * {@inheritDoc}
     */
    protected function getFileUrl($path, $filter)
    {
        return $this->cachePrefix.'/'.$filter.'/'.$path;
    }
}

