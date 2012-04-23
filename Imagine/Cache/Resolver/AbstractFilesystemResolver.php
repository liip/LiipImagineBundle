<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Filesystem\Filesystem;

abstract class AbstractFilesystemResolver implements ResolverInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

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
     * Stores the content into a static file.
     *
     * @throws \RuntimeException
     *
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
