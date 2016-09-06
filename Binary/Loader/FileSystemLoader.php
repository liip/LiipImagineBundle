<?php

namespace Liip\ImagineBundle\Binary\Loader;

use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Liip\ImagineBundle\Model\FileBinary;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;

class FileSystemLoader implements LoaderInterface
{
    /**
     * @var MimeTypeGuesserInterface
     */
    protected $mimeTypeGuesser;

    /**
     * @var ExtensionGuesserInterface
     */
    protected $extensionGuesser;

    /**
     * @var string
     */
    protected $rootPath;

    /**
     * @param MimeTypeGuesserInterface  $mimeTypeGuesser
     * @param ExtensionGuesserInterface $extensionGuesser
     * @param string                    $rootPath
     */
    public function __construct(
        MimeTypeGuesserInterface $mimeTypeGuesser,
        ExtensionGuesserInterface $extensionGuesser,
        $rootPath
    ) {
        $this->mimeTypeGuesser = $mimeTypeGuesser;
        $this->extensionGuesser = $extensionGuesser;

        if (empty($rootPath) || !($realRootPath = realpath($rootPath))) {
            throw new InvalidArgumentException(sprintf('Root image path not resolvable "%s"', $rootPath));
        }

        $this->rootPath = $realRootPath;
    }

    /**
     * {@inheritdoc}
     */
    public function find($path)
    {
        if (!($absolutePath = realpath($this->rootPath.DIRECTORY_SEPARATOR.$path))) {
            throw new NotLoadableException(sprintf('Source image not resolvable "%s"', $path));
        }

        if (0 !== strpos($absolutePath, $this->rootPath)) {
            throw new NotLoadableException(sprintf('Source image invalid "%s" as it is outside of the defined root path', $absolutePath));
        }

        $mimeType = $this->mimeTypeGuesser->guess($absolutePath);

        return new FileBinary(
            $absolutePath,
            $mimeType,
            $this->extensionGuesser->guess($mimeType)
        );
    }
}
