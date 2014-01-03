<?php

namespace Liip\ImagineBundle\Imagine\Filter;

use Imagine\Image\Color;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;

class BackgroundFilter extends AbstractConfigurableFilter
{
    protected $imagine;

    public function __construct(ImagineInterface $imagine)
    {
        $this->imagine = $imagine;
    }

    public function apply(ImageInterface $image)
    {
        $background = new Color(isset($this->options['color']) ? $this->options['color'] : '#fff');
        $topLeft = new Point(0, 0);

        $canvas = $this->imagine->create($image->getSize(), $background);

        return $canvas->paste($image, $topLeft);
    }
}
