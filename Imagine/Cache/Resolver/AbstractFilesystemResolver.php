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

    /**
     * Removes a stored image resource.
     *
     * @param string $targetPath The target path provided by the resolve method.
     * @param string $filter The name of the imagine filter in effect.
     *
     * @return bool Whether the file has been removed successfully.
     */
    public function remove($targetPath, $filter)
    {
        $filename = $this->getFilePath($targetPath, $filter);
        $this->filesystem->remove($filename);

        return file_exists($filename);
    }

    /**
     * Return the local filepath.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @param string $path The resource path to convert.
     * @param string $filter The name of the imagine filter.
     * @param string $basePath An optional base path to remove from the path.
     *
     * @return string
     */
    abstract protected function getFilePath($path, $filter, $basePath = '');
}
