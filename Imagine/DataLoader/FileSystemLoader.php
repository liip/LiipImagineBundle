<?php

namespace Avalanche\Bundle\ImagineBundle\Imagine\DataLoader;

class FileSystemLoader implements LoaderInterface
{
    /**
     * @var string
     */
    private $webRoot;

    /**
     * @var array
     */
    private $formats;

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

    public function find($path)
    {
        $path = '/'.ltrim($path, '/');
        $name = explode('.', $path);
        if (count($name) > 1) {
            $targetFormat = array_pop($name);
            if (!in_array($targetFormat, $this->formats)) {
                if ('orig' !== $targetFormat) {
                    return array(false, false, false);
                }
                $targetFormat = null;
            }
            $name = implode('.', $name);
        } else {
            $targetFormat = null;
            $name = $path;
        }

        if (empty($targetFormat) || !file_exists($this->webRoot.$path)) {
            // attempt to find format
            $found = false;
            foreach ($this->formats as $format) {
                if ($format !== $targetFormat
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
                return array(false, false, false);
            }
        }

        return array($path, $this->webRoot.$path, $targetFormat);
    }
}
