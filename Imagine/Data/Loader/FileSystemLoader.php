<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Imagine\Image\ImagineInterface;

class FileSystemLoader implements LoaderInterface
{
    /**
     * @var Imagine\Image\ImagineInterface
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
     * Constructs
     *
     * @param ImagineInterface  $imagine
     * @param array             $formats
     * @param string            $rootPath
     */
    public function __construct(ImagineInterface $imagine, $formats, $rootPath)
    {
        $this->imagine = $imagine;
        $this->formats = $formats;
        $this->rootPath = realpath($rootPath);
    }

    /**
     * Get the file info for the given path
     *
     * This can optionally be used to generate the given file
     *
     * @param $absolutePath
     * @return array
     */
    protected function getFileInfo($absolutePath)
    {
        return pathinfo($absolutePath);
    }

    /**
     * @param string $path
     *
     * @return Imagine\Image\ImageInterface
     */
    public function find($path)
    {
        if (false !== strpos($path, '/../') || 0 === strpos($path, '../')) {
            throw new NotFoundHttpException(sprintf("Source image was searched with '%s' out side of the defined root path", $path));
        }

        $file = $this->rootPath.'/'.ltrim($path, '/');
        $info = $this->getFileInfo($file);
        $absolutePath = $info['dirname'].'/'.$info['basename'];

        $name = $info['dirname'].'/'.$info['filename'];
        $targetFormat = empty($this->formats) || in_array($info['extension'], $this->formats)
            ? $info['extension'] : null;

        if (empty($targetFormat) || !file_exists($absolutePath)) {
            // attempt to determine path and format
            $absolutePath = null;
            foreach ($this->formats as $format) {
                if ($targetFormat !== $format
                    && file_exists($name.'.'.$format)
                ) {
                    $absolutePath = $name.'.'.$format;
                    break;
                }
            }

            if (!$absolutePath) {
                if (!empty($targetFormat) && is_file($name)) {
                    $absolutePath = $name;
                } else {
                    throw new NotFoundHttpException(sprintf('Source image not found in "%s"', $absolutePath));
                }
            }
        }

        return $this->imagine->open($absolutePath);
    }
}
