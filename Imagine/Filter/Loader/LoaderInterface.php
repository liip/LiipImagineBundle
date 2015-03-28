<?php

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Image\ImageInterface;

interface LoaderInterface
{
    /**
     * Loads and applies a filter on the given image.
     *
     * @param ImageInterface $image
     * @param array          $options
     *
     * @return ImageInterface
     */
    public function load(ImageInterface $image, array $options = array());
}
