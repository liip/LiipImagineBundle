<?php

namespace Liip\ImagineBundle\Imagine\DataLoader;

interface LoaderInterface
{
    /**
     * @param string $path
     *
     * @return Imagine\Image\ImageInterface
     */
    function find($path);
}
