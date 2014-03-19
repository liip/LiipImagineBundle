<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @var array
     */
    protected $formats;

    /**
     * @var string
     */
    protected $rootPath;

    /**
     * @param MimeTypeGuesserInterface  $mimeTypeGuesser
     * @param ExtensionGuesserInterface $extensionGuesser
     * @param array                     $formats
     * @param string                    $rootPath
     */
    public function __construct(
        MimeTypeGuesserInterface $mimeTypeGuesser,
        ExtensionGuesserInterface $extensionGuesser,
        array $formats,
        $rootPath
    ){
        $this->formats = $formats;
        $this->rootPath = realpath($rootPath);
        $this->mimeTypeGuesser = $mimeTypeGuesser;
        $this->extensionGuesser = $extensionGuesser;
    }

    /**
     * Get the file info for the given path.
     *
     * This can optionally be used to generate the given file.
     *
     * @param string $absolutePath
     *
     * @return array
     */
    protected function getFileInfo($absolutePath)
    {
        return pathinfo($absolutePath);
    }

    /**
     * {@inheritDoc}
     */
    public function find($path)
    {
        if (false !== strpos($path, '/../') || 0 === strpos($path, '../')) {
            throw new NotFoundHttpException(sprintf("Source image was searched with '%s' out side of the defined root path", $path));
        }

        $file = $this->rootPath.'/'.ltrim($path, '/');
        $info = $this->getFileInfo($file);
        $absolutePath = $info['dirname'].DIRECTORY_SEPARATOR.$info['basename'];
        if (!file_exists($absolutePath)) {
            $name = $info['dirname'].DIRECTORY_SEPARATOR.$info['filename'];
            // attempt to determine path and format
            $absolutePath = null;
            foreach ($this->formats as $format) {
                if (file_exists($name.'.'.$format)) {
                    $absolutePath = $name.'.'.$format;
                    break;
                }
            }
            if (null === $absolutePath) {
                if (file_exists($name)) {
                    $absolutePath = $name;
                } else {
                    throw new NotFoundHttpException(sprintf('Source image not found in "%s"', $file));
                }
            }
        }

        $mimeType = $this->mimeTypeGuesser->guess($absolutePath);

        return new Binary(
            file_get_contents($absolutePath),
            $mimeType,
            $this->extensionGuesser->guess($mimeType)
        );
    }
}
