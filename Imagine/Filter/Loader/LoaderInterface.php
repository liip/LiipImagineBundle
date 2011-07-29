<?php

namespace Avalanche\Bundle\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Image\ImageInterface;

interface LoaderInterface
{
    /**
     * @param Imagine\Image\ImagineInterface $image
     * @param array $options
     *
     * @return Imagine\Filter\FilterInterface
     */
    function load(ImageInterface $image, array $options = array());
}
