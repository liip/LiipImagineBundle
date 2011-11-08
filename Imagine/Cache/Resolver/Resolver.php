<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\CacheManagerAwareInterface,
    Liip\ImagineBundle\Imagine\Cache\CacheManager;

use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpKernel\Util\Filesystem;

abstract class Resolver implements ResolverInterface, CacheManagerAwareInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var CacheManager;
     */
    protected $cacheManager;

    /**
     * Constructs cache web path resolver
     *
     * @param Filesystem  $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem   = $filesystem;
    }

    /**
     * @param CacheManager $cacheManager
     */
    public function setCacheManager(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @throws \RuntimeException
     * @param Response $response
     * @param string $targetPath
     * @param string $filter
     *
     * @return Response
     */
    public function store(Response $response, $targetPath, $filter)
    {
        $dir = pathinfo($targetPath, PATHINFO_DIRNAME);

        if (!is_dir($dir) && !$this->filesystem->mkdir($dir)) {
            throw new \RuntimeException(sprintf(
                'Could not create directory %s', $dir
            ));
        }

        file_put_contents($targetPath, $response->getContent());

        $response->setStatusCode(201);

        return $response;
    }
}
