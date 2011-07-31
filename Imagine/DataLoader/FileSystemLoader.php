<?php

namespace Avalanche\Bundle\ImagineBundle\Imagine\DataLoader;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileSystemLoader implements LoaderInterface
{
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
     * @param string    $webRoot
     * @param array     $formats
     */
    public function __construct($webRoot, $formats)
    {
        $this->webRoot = $webRoot;
        $this->formats = $formats;
    }

    protected function splitPath($path)
    {
        $name = explode('.', $path);
        if (count($name) > 1) {
            $format = array_pop($name);
            if (!in_array($format, $this->formats)) {
                return  array($path, null);
            }
            $name = implode('.', $name);
        } else {
            $format = null;
            $name = $path;
        }

        return array($name, $format);
    }

    public function find($path)
    {
        $path = '/'.ltrim($path, '/');
        list($name, $targetFormat) = $this->splitPath($path);
        if (!$name) {
            throw new NotFoundHttpException(sprintf('Source image not found in "%s"', $path));
        }

        if (empty($targetFormat) || !file_exists($this->webRoot.$path)) {
            // attempt to determine path and format
            $found = false;
            foreach ($this->formats as $format) {
                if ($targetFormat !== $format
                    && file_exists($this->webRoot.$name.'.'.$format)
                ) {
                    $path = $name.'.'.$format;
                    if (empty($targetFormat)) {
                        $targetFormat = $format;
                    }
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                throw new NotFoundHttpException(sprintf('Source image not found in "%s"', $path));
            }
        }

        return array($path, $this->webRoot.$path, $targetFormat);
    }
}
