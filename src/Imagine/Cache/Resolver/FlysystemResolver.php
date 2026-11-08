<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use League\Flysystem\AdapterInterface;
use League\Flysystem\FilesystemInterface;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException;
use Symfony\Component\Routing\RequestContext;

class FlysystemResolver implements ResolverInterface
{
    protected FilesystemInterface $flysystem;

    protected RequestContext $requestContext;

    protected string $webRoot;

    protected string $cachePrefix;

    protected string $cacheRoot;

    /**
     * Flysystem specific visibility.
     *
     * @see AdapterInterface
     */
    protected string $visibility;

    /**
     * FlysystemResolver constructor.
     */
    public function __construct(
        FilesystemInterface $flysystem,
        RequestContext $requestContext,
        string $rootUrl,
        string $cachePrefix = 'media/cache',
        string $visibility = AdapterInterface::VISIBILITY_PUBLIC,
    ) {
        $this->flysystem = $flysystem;
        $this->requestContext = $requestContext;

        $this->webRoot = rtrim($rootUrl, '/');
        $this->cachePrefix = ltrim(str_replace('//', '/', $cachePrefix), '/');
        $this->cacheRoot = $this->cachePrefix;
        $this->visibility = $visibility;
    }

    /**
     * Checks whether the given path is stored within this Resolver.
     */
    public function isStored(string $path, string $filter): bool
    {
        return $this->flysystem->has($this->getFilePath($path, $filter));
    }

    /**
     * Resolves filtered path for rendering in the browser.
     *
     * @param string $path   The path where the original file is expected to be
     * @param string $filter The name of the imagine filter in effect
     *
     * @throws NotResolvableException
     *
     * @return string The absolute URL of the cached image
     */
    public function resolve(string $path, string $filter): string
    {
        return \sprintf(
            '%s/%s',
            rtrim($this->webRoot, '/'),
            ltrim($this->getFileUrl($path, $filter), '/')
        );
    }

    /**
     * Stores the content of the given binary.
     *
     * @param BinaryInterface $binary The image binary to store
     * @param string          $path   The path where the original file is expected to be
     * @param string          $filter The name of the imagine filter in effect
     */
    public function store(BinaryInterface $binary, string $path, string $filter): void
    {
        $this->flysystem->put(
            $this->getFilePath($path, $filter),
            $binary->getContent(),
            ['visibility' => $this->visibility, 'mimetype' => $binary->getMimeType()]
        );
    }

    /**
     * @param string[] $paths   The paths where the original files are expected to be
     * @param string[] $filters The imagine filters in effect
     */
    public function remove(array $paths, array $filters): void
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

    protected function getFilePath(string $path, string $filter): string
    {
        return $this->getFileUrl($path, $filter);
    }

    protected function getFileUrl(string $path, string $filter): string
    {
        // crude way of sanitizing URL scheme ("protocol") part
        $path = str_replace('://', '---', $path);

        return $this->cachePrefix.'/'.$filter.'/'.ltrim($path, '/');
    }
}
