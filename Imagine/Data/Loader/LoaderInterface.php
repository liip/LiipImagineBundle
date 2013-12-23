<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Liip\ImagineBundle\Imagine\RawImage;

interface LoaderInterface
{
    /**
     * Retrieve the Image represented by the given path.
     *
     * The path may be a file path on a filesystem, or any unique identifier among the storage engine implemented by this Loader.
     *
     * @param mixed $path
     *
     * @return RawImage|string An image content
     */
    function find($path);
}
