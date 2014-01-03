<?php

namespace Liip\ImagineBundle\Imagine\Filter;

use Imagine\Filter\Basic\Resize;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;

/**
 * @author Maxime Colin <contact@maximecolin.fr>
 */
class UpscaleFilter extends AbstractConfigurableFilter
{
    public function apply(ImageInterface $image)
    {
        if (!isset($this->options['min'])) {
            throw new \InvalidArgumentException('Missing min option.');
        }

        list($width, $height) = $this->options['min'];

        $size = $image->getSize();
        $origWidth = $size->getWidth();
        $origHeight = $size->getHeight();

        if ($origWidth < $width || $origHeight < $height) {
            $widthRatio = $width / $origWidth ;
            $heightRatio = $height / $origHeight;

            $ratio = $widthRatio > $heightRatio ? $widthRatio : $heightRatio;

            $filter = new Resize(new Box($origWidth * $ratio, $origHeight * $ratio));

            return $filter->apply($image);
        }

        return $image;
    }
}
