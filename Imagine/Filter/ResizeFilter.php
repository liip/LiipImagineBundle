<?php

namespace Liip\ImagineBundle\Imagine\Filter;

use Imagine\Filter\Basic\Resize;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;

/**
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class ResizeFilter extends AbstractConfigurableFilter
{
    public function apply(ImageInterface $image)
    {
        list($width, $height) = $this->options['size'];

        $filter = new Resize(new Box($width, $height));

        return $filter->apply($image);
    }
}
