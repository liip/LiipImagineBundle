<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Gaufrette\Filesystem;
use Imagine\Image\ImagineInterface;

class GaufretteFilesystemLoader implements LoaderInterface
{
    /**
     * @var ImagineInterface
     */
    protected $imagine;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct(ImagineInterface $imagine, Filesystem $filesystem)
    {
        $this->imagine = $imagine;
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $path
     *
     * @return \Imagine\Image\ImageInterface
     */
    public function find($path)
    {
        return $this->imagine->load($this->filesystem->read($path));
    }
}
