<?php

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Image\ImageInterface;

use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;
use Prezent\AppBundle\Imagine\Filter\AspectResize;

/**
 * Loader for this bundle's two-way relative resize filter.
 *
 * @author      Robert-Jan Bijl <r.j.bijl@gmail.com>
 */
class TwoWayRelativeResizeFilterLoader implements LoaderInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ImageInterface $image, array $options = array())
    {
        $newHeight = isset($options['height']) ? $options['height'] : null;
        $newWidth = isset($options['width']) ? $options['width'] : null;

        // if no resize is given, just return the original image
        if (null === $newHeight && null === $newWidth) {
            return $image;
        }

        $filter = new TwoWayRelativeResize($newHeight, $newWidth);
        return $filter->apply($image);
    }
}
