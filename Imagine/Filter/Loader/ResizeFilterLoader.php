<?php

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Filter\Basic\Resize;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;

/**
 * Loader for Imagine's basic resize filter.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class ResizeFilterLoader implements LoaderInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ImageInterface $image, array $options = array())
    {
        list($width, $height) = $options['size'];

        $filter = new Resize(new Box($width, $height));

        return $filter->apply($image);
    }
}
