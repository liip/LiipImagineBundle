<?php

namespace Liip\ImagineBundle\Imagine\Filter;

use Imagine\Filter\Basic\Crop;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;

class CropFilter extends AbstractConfigurableFilter
{
    public function apply(ImageInterface $image)
    {
        list($x, $y) = $this->options['start'];
        list($width, $height) = $this->options['size'];

        $filter = new Crop(new Point($x, $y), new Box($width, $height));

        return $filter->apply($image);
    }
}
