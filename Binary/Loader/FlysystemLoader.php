<?php

namespace Liip\ImagineBundle\Binary\Loader;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;

class FlysystemLoader implements LoaderInterface
{
    /**
     * @var \League\Flysystem\Filesystem
     */
    protected $filesystem;
    
    /**
     * @var ExtensionGuesserInterface
     */
    protected $extensionGuesser;
    

    public function __construct(
        ContainerInterface $container,
        ExtensionGuesserInterface $extensionGuesser,
        $sFileSystem)
    {
        $this->extensionGuesser = $extensionGuesser;
        
        try {
            $sFileSystemService = sprintf('oneup_flysystem.%s_filesystem', $sFileSystem);
            $this->filesystem = $container->get($sFileSystemService);
        } catch (ServiceNotFoundException $ex) {
            throw new NotLoadableException(sprintf("Flysystem '%s' was not found. Tried to load '%s' service.", $sFileSystem, $sFileSystemService));
        }
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
