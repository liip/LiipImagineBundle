<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Imagine\Image\ImagineInterface;

class FileSystemLoader implements LoaderInterface
{
    /**
     * @var Imagine\Image\ImagineInterface
     */
    private $imagine;

    /**
     * @var array
     */
    private $formats;

    /**
     * @var string
     */
    private $rootPath;

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
     * @param string $path
     *
     * @return Imagine\Image\ImageInterface
     */
    public function find($path)
    {
        $path = $this->rootPath.'/'.ltrim($path, '/');;
        if (0 !== strpos($path, $this->rootPath)) {
            throw new NotFoundHttpException(sprintf("Source image was searched with '%s' out side of the defined root path", $path));
        }

        $info = pathinfo($path);

        $name = $info['dirname'].'/'.$info['filename'];
        $targetFormat = empty($this->formats) || in_array($info['extension'], $this->formats)
            ? $info['extension'] : null;

        if (empty($targetFormat) || !file_exists($path)) {
            // attempt to determine path and format
            $path = null;
            foreach ($this->formats as $format) {
                if ($targetFormat !== $format
                    && file_exists($name.'.'.$format)
                ) {
                    $path = $name.'.'.$format;
                    break;
                }
            }

            if (!$path) {
                throw new NotFoundHttpException(sprintf('Source image not found in "%s"', $path));
            }
        }

        return $this->imagine->open($path);
    }
}
