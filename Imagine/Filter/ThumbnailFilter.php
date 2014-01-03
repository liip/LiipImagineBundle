<?php

namespace Liip\ImagineBundle\Imagine\Filter;

use Imagine\Filter\Basic\Thumbnail;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;

class ThumbnailFilter extends AbstractConfigurableFilter
{
    public function apply(ImageInterface $image)
    {
        $mode = ImageInterface::THUMBNAIL_OUTBOUND;
        if (!empty($this->options['mode']) && 'inset' === $this->options['mode']) {
            $mode = ImageInterface::THUMBNAIL_INSET;
        }

        list($width, $height) = $this->options['size'];

        $size = $image->getSize();
        $origWidth = $size->getWidth();
        $origHeight = $size->getHeight();

        if (null === $width || null === $height) {
            if (null === $height) {
                $height = (int) (($width / $origWidth) * $origHeight);
            } elseif (null === $width) {
                $width = (int) (($height / $origHeight) * $origWidth);
            }
        }

        if (($origWidth > $width || $origHeight > $height)
            || (!empty($this->options['allow_upscale']) && ($origWidth !== $width || $origHeight !== $height))
        ) {
            $filter = new Thumbnail(new Box($width, $height), $mode);
            $image = $filter->apply($image);
        }

        return $image;
    }
}
