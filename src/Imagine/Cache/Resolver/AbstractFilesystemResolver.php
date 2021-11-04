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

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Cache\CacheManagerAwareInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractFilesystemResolver implements ResolverInterface, CacheManagerAwareInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $basePath = '';

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var int
     */
    protected $folderPermissions = 0777;
    /**
     * @var Request
     */
    private $request;

    /**
     * Constructs a filesystem based cache resolver.
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    public function setCacheManager(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * Set the base path to.
     *
     * @param $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * @param int $folderPermissions
     */
    public function setFolderPermissions($folderPermissions)
    {
        $this->folderPermissions = $folderPermissions;
    }

    /**
     * {@inheritdoc}
     */
    public function isStored($path, $filter)
    {
        return file_exists($this->getFilePath($path, $filter));
    }

    /**
     * {@inheritdoc}
     */
    public function store(BinaryInterface $binary, $path, $filter)
    {
        $filePath = $this->getFilePath($path, $filter);

        $dir = pathinfo($filePath, PATHINFO_DIRNAME);

        $this->makeFolder($dir);

        file_put_contents($filePath, $binary->getContent());
    }

    /**
     * {@inheritdoc}
     */
    public function remove(array $paths, array $filters)
    {
        if (empty($paths) && empty($filters)) {
            return;
        }

        // TODO: this logic has to be refactored.
        [$rootCachePath] = explode(current($filters), $this->getFilePath('whateverpath', current($filters)));

        if (empty($paths)) {
            $filtersCachePaths = [];
            foreach ($filters as $filter) {
                $filterCachePath = $rootCachePath.$filter;
                if (is_dir($filterCachePath)) {
                    $filtersCachePaths[] = $filterCachePath;
                }
            }

            $this->filesystem->remove($filtersCachePaths);

            return;
        }

        foreach ($paths as $path) {
            foreach ($filters as $filter) {
                $this->filesystem->remove($this->getFilePath($path, $filter));
            }
        }
    }

    /**
     * @throws \LogicException
     *
     * @return Request
     */
    protected function getRequest()
    {
        if (false === $this->request) {
            throw new \LogicException('The request was not injected, inject it before using resolver.');
        }

        return $this->request;
    }

    /**
     * @param string $dir
     *
     * @throws \RuntimeException
     */
    protected function makeFolder($dir)
    {
        if (!is_dir($dir)) {
            $parent = \dirname($dir);
            try {
                $this->makeFolder($parent);
                $this->filesystem->mkdir($dir);
                $this->filesystem->chmod($dir, $this->folderPermissions);
            } catch (IOException $e) {
                throw new \RuntimeException(sprintf('Could not create directory %s', $dir), 0, $e);
            }
        }
    }

    /**
     * Return the local filepath.
     *
     * @param string $path   The resource path to convert
     * @param string $filter The name of the imagine filter
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return string
     */
    abstract protected function getFilePath($path, $filter);
}
