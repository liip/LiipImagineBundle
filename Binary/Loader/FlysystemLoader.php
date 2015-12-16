<?php

namespace Liip\ImagineBundle\Binary\Loader;

use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use League\Flysystem\Filesystem;

class FlysystemLoader implements LoaderInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;
    
    /**
     * @var ExtensionGuesserInterface
     */
    protected $extensionGuesser;
    

    public function __construct(
        ExtensionGuesserInterface $extensionGuesser,
        Filesystem $filesystem)
    {
        $this->extensionGuesser = $extensionGuesser;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritDoc}
     */
    public function find($path)
    {
        if ($this->filesystem->has($path) === false) {
            throw new NotLoadableException(sprintf('Source image "%s" not found.', $path));
        }

        $mimeType = $this->filesystem->getMimetype($path);

        return new Binary(
            $this->filesystem->read($path),
            $mimeType,
            $this->extensionGuesser->guess($mimeType)
        );
    }
}
