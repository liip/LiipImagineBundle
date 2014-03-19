<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Imagine\Image\ImagineInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileSystemLoader implements LoaderInterface
{
    /**
     * @var ImagineInterface
     */
    protected $imagine;

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
     * @param ImagineInterface  $imagine
     * @param array             $formats
     * @param string            $rootPath
     */
    public function __construct(ImagineInterface $imagine, array $formats, $rootPath)
    {
        $this->imagine = $imagine;
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
        if (!file_exists($absolutePath)) {
            // attempt to determine path and format
            $name = $info['dirname'].DIRECTORY_SEPARATOR.$info['filename'];
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

        return $this->imagine->open($absolutePath);
    }
}
