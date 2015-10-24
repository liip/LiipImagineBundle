<?php

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Filter\Basic\Crop;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class CropFilterLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ImageInterface $image, array $options = array())
    {
        list($x, $y) = $options['start'];
        list($width, $height) = $options['size'];

        $filter = new Crop(new Point($x, $y), new Box($width, $height));
        $image = $filter->apply($image);

        return $image;
    }
}
