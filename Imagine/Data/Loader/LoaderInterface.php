<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

interface LoaderInterface
{
    /**
     * @param string $path
     *
     * @return Imagine\Image\ImageInterface
     */
    function find($path);
}
