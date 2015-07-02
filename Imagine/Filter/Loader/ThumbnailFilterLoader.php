<?php

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Filter\Basic\Thumbnail;
use Imagine\Filter\Basic\Resize;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;

class ThumbnailFilterLoader implements LoaderInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ImageInterface $image, array $options = array())
    {
        $mode = ImageInterface::THUMBNAIL_OUTBOUND;
        if (!empty($options['mode']) && 'inset' === $options['mode']) {
            $mode = ImageInterface::THUMBNAIL_INSET;
        }

        if (!empty($options['filter'])) {
            $filter = constant('Imagine\Image\ImageInterface::FILTER_'.strtoupper($options['filter']));
        }
        if (empty($filter)) {
            $filter = ImageInterface::FILTER_UNDEFINED;
        }

        list($width, $height) = $options['size'];

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
            || (!empty($options['allow_upscale']) && ($origWidth !== $width || $origHeight !== $height))
        ) {
            if(($origWidth < $width || $origHeight < $height)){
                // resize first
                $ratio = max($width / $origWidth, $height / $origHeight);
                $resizeFilter = new Resize(new Box(round($origWidth * $ratio), round($origHeight * $ratio)));
                $image = $resizeFilter->apply($image);
            }
            $filter = new Thumbnail(new Box($width, $height), $mode, $filter);
            $image = $filter->apply($image);
        }

        return $image;
    }
}
