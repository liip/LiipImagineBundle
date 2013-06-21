<?php

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Filter\Basic\Thumbnail;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;

class ThumbnailFilterLoader implements LoaderInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ImageInterface $image, array $options = array())
    {
        $mode = $options['mode'] === 'inset' ?
            ImageInterface::THUMBNAIL_INSET :
            ImageInterface::THUMBNAIL_OUTBOUND;
        list($width, $height) = $options['size'];

        $size = $image->getSize();
        $origWidth = $size->getWidth();
        $origHeight = $size->getHeight();


        if (null === $width || null === $height) {
            if (null === $height) {
                $height = (int)(($width / $origWidth) * $origHeight);
            } else if (null === $width) {
                $width = (int)(($height / $origHeight) * $origWidth);
            }
        }

        if (($origWidth > $width || $origHeight > $height)
            || (!empty($options['allow_upscale']) && ($origWidth !== $width || $origHeight !== $height))
        ) {
            $filter = new Thumbnail(new Box($width, $height), $mode);
            $image = $filter->apply($image);
        }

        return $image;
    }
}
