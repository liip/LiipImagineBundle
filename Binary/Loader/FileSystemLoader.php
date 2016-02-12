<?php

namespace Liip\ImagineBundle\Binary\Loader;

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

        $this->rootPath = rtrim($rootPath, '/');
    }

    /**
     * {@inheritdoc}
     */
    public function find($path)
    {
        if (false !== strpos($path, '../')) {
            throw new NotLoadableException(sprintf("Source image was searched with '%s' out side of the defined root path", $path));
        }

        $absolutePath = $this->rootPath.'/'.ltrim($path, '/');

        if (false == file_exists($absolutePath)) {
            throw new NotLoadableException(sprintf('Source image not found in "%s"', $absolutePath));
        }

        $mimeType = $this->mimeTypeGuesser->guess($absolutePath);

        return new FileBinary(
            $absolutePath,
            $mimeType,
            $this->extensionGuesser->guess($mimeType)
        );
    }
}
