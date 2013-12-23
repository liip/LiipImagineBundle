<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Liip\ImagineBundle\Imagine\RawImage;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileSystemLoader implements LoaderInterface
{
    /**
     * @var array
     */
    protected $formats;

    /**
     * @var string
     */
    protected $rootPath;

    /**
     * Constructor.
     *
     * @param array             $formats
     * @param string            $rootPath
     */
    public function __construct(array $formats, $rootPath)
    {
        $this->formats = $formats;
        $this->rootPath = realpath($rootPath);
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

        $name = $info['dirname'].DIRECTORY_SEPARATOR.$info['filename'];

        $targetFormat = null;
        // set a format if an extension is found and is allowed
        if (isset($info['extension'])
            && (empty($this->formats) || in_array($info['extension'], $this->formats))
        ) {
            $targetFormat = $info['extension'];
        }

        if (empty($targetFormat) || !file_exists($absolutePath)) {
            // attempt to determine path and format
            $absolutePath = null;
            foreach ($this->formats as $format) {
                if ($targetFormat !== $format && file_exists($name.'.'.$format)) {
                    $absolutePath = $name.'.'.$format;

                    break;
                }
            }

            if (!$absolutePath) {
                if (!empty($targetFormat) && is_file($name)) {
                    $absolutePath = $name;
                } else {
                    throw new NotFoundHttpException(sprintf('Source image not found in "%s"', $file));
                }
            }
        }

        return new RawImage(
            file_get_contents($absolutePath),
            MimeTypeGuesser::getInstance()->guess($absolutePath)
        );
    }
}
