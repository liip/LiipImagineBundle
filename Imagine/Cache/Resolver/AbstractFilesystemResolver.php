<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Cache\CacheManagerAwareInterface;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractFilesystemResolver implements ResolverInterface, CacheManagerAwareInterface
{
    /**
     * @var Request
     */
    private $request;

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
     * Constructs a filesystem based cache resolver.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem   = $filesystem;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * @param CacheManager $cacheManager
     */
    public function setCacheManager(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * Set the base path to
     *
     * @param $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * @param int $mkdirMode
     */
    public function setFolderPermissions ($folderPermissions)
    {
        $this->folderPermissions = $folderPermissions;
    }

    /**
     * {@inheritDoc}
     */
    public function isStored($path, $filter)
    {
        return file_exists($this->getFilePath($path, $filter));
    }

    /**
     * {@inheritDoc}
     */
    public function store(Response $response, $path, $filter)
    {
        $filePath = $this->getFilePath($path, $filter);

        $dir = pathinfo($filePath, PATHINFO_DIRNAME);

        $this->makeFolder($dir);

        file_put_contents($filePath, $response->getContent());

        $response->setStatusCode(201);

        return $response;
    }

    /**
     * Removes a stored image resource.
     *
     * @param string $path The target path provided by the resolve method.
     * @param string $filter The name of the imagine filter in effect.
     *
     * @return bool Whether the file has been removed successfully.
     */
    public function remove($path, $filter)
    {
        $this->basePath = $this->getRequest()->getBaseUrl();
        $filePath = $this->getFilePath($path, $filter);

        $this->filesystem->remove($filePath);

        return !file_exists($filePath);
    }

    /**
     * @return Request
     *
     * @throws \LogicException
     */
    protected function getRequest()
    {
        if (false == $this->request) {
            throw new \LogicException('The request was not injected, inject it before using resolver.');
        }

        return $this->request;
    }

    /**
     * @param string $dir
     * @throws \RuntimeException
     */
    protected function makeFolder($dir)
    {
        if (!is_dir($dir)) {
            $parent = dirname($dir);
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
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @param string $path The resource path to convert.
     * @param string $filter The name of the imagine filter.
     *
     * @return string
     */
    abstract protected function getFilePath($path, $filter);
}
