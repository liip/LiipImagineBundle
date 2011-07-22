<?php

namespace Avalanche\Bundle\ImagineBundle\Imagine\Filter\Loader;

use Imagine\ImageInterface;

interface LoaderInterface
{
    /**
     * @param Imagine\ImageInterface $image
     * @param array $options
     *
     * @return Imagine\Filter\FilterInterface
     */
    function load(ImageInterface $image, array $options = array());
}
