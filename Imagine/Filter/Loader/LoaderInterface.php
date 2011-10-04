<?php

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Image\ImageInterface;

interface LoaderInterface
{
    /**
     * @param Imagine\Image\ImagineInterface $image
     * @param array $options
     *
     * @return Imagine\Image\ImageInterface
     */
    function load(ImageInterface $image, array $options = array());
}
