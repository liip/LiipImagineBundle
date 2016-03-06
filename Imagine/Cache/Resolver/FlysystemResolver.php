<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use League\Flysystem\Filesystem;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException;
use Symfony\Component\Routing\RequestContext;

class FlysystemResolver implements ResolverInterface
{
    /**
     * @var Filesystem
     */
    protected $flysystem;

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
     * FlysystemResolver constructor.
     *
     * @param Filesystem     $flysystem
     * @param RequestContext $requestContext
     * @param $rootUrl
     * @param string $cachePrefix
     */
    public function __construct(
        Filesystem $flysystem,
        RequestContext $requestContext,
        $rootUrl,
        $cachePrefix = 'media/cache'
    ) {
        $this->flysystem = $flysystem;
        $this->requestContext = $requestContext;

        $this->webRoot = rtrim($rootUrl, '/');
        $this->cachePrefix = ltrim(str_replace('//', '/', $cachePrefix), '/');
        $this->cacheRoot = $this->cachePrefix;
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
        return $this->flysystem->has($this->getFilePath($path, $filter));
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilePath($path, $filter)
    {
        return $this->getFileUrl($path, $filter);
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

    /**
     * Resolves filtered path for rendering in the browser.
     *
     * @param string $path   The path where the original file is expected to be.
     * @param string $filter The name of the imagine filter in effect.
     *
     * @return string The absolute URL of the cached image.
     *
     * @throws NotResolvableException
     */
    public function resolve($path, $filter)
    {
        return sprintf(
            '%s/%s',
            $this->webRoot,
            $this->getFileUrl($path, $filter)
        );
    }

    /**
     * Stores the content of the given binary.
     *
     * @param BinaryInterface $binary The image binary to store.
     * @param string          $path   The path where the original file is expected to be.
     * @param string          $filter The name of the imagine filter in effect.
     */
    public function store(BinaryInterface $binary, $path, $filter)
    {
        $this->flysystem->put(
            $this->getFilePath($path, $filter),
            $binary->getContent()
        );
    }

    /**
     * @param string[] $paths   The paths where the original files are expected to be.
     * @param string[] $filters The imagine filters in effect.
     */
    public function remove(array $paths, array $filters)
    {
        if (empty($paths) && empty($filters)) {
            return;
        }

        if (empty($paths)) {
            foreach ($filters as $filter) {
                $filterCacheDir = $this->cacheRoot.'/'.$filter;
                $this->flysystem->deleteDir($filterCacheDir);
            }

            return;
        }

        foreach ($paths as $path) {
            foreach ($filters as $filter) {
                if ($this->flysystem->has($this->getFilePath($path, $filter))) {
                    $this->flysystem->delete($this->getFilePath($path, $filter));
                }
            }
        }
    }
}
