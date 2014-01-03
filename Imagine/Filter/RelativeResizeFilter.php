<?php

namespace Liip\ImagineBundle\Imagine\Filter;

use Imagine\Filter\Advanced\RelativeResize;
use Imagine\Image\ImageInterface;

/**
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class RelativeResizeFilter extends AbstractConfigurableFilter
{
    public function apply(ImageInterface $image)
    {
        $filter = new RelativeResize($this->options['method'], $this->options['parameter']);

        return $filter->apply($image);
    }
}
