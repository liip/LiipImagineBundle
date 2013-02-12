<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

interface LoaderInterface
{
    /**
     * Retrieve the Image represented by the given path.
     *
     * @param string $path
     *
     * @return \Imagine\Image\ImageInterface
     */
    function find($path);
}
