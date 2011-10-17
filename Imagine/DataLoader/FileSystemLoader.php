<?php

namespace Liip\ImagineBundle\Imagine\DataLoader;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Imagine\Image\ImagineInterface;

class FileSystemLoader implements LoaderInterface
{
    /**
     * @var Imagine\Image\ImagineInterface
     */
    private $imagine;

    /**
     * @var string
     */
    protected $webRoot;

    /**
     * @var array
     */
    protected $formats;

    /**
     * Constructs
     *
     * @param Imagine\Image\ImagineInterface $imagine
     * @param string    $webRoot
     * @param array     $formats
     */
    public function __construct(ImagineInterface $imagine, $webRoot, $formats)
    {
        $this->imagine = $imagine;
        $this->webRoot = realpath($webRoot);
        $this->formats = $formats;
    }

    public function find($path)
    {
        $path = $this->webRoot.'/'.ltrim($path, '/');
        $info = pathinfo($path);
        if (!$info) {
            throw new NotFoundHttpException(sprintf('Source image not found in "%s"', $path));
        }

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
                    if (empty($targetFormat)) {
                        $targetFormat = $format;
                    }
                    break;
                }
            }

            if (!$path) {
                throw new NotFoundHttpException(sprintf('Source image not found in "%s"', $path));
            }
        }

        $image = $this->imagine->open($path);
        return array($path, $image, $targetFormat);
    }
}
