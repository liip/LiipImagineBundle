<?php

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Filter\Advanced\Grayscale;
use Imagine\Image\ImageInterface;

/**
 * GrayscaleFilterLoader - apply grayscale filter.
 *
 * @author Gregoire Humeau <gregoire.humeau@gmail.com>
 */
class GrayscaleFilterLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ImageInterface $image, array $options = array())
    {
        $filter = new Grayscale();

        return $filter->apply($image);
    }
}
