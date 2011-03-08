<?php

namespace Avalanche\Bundle\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Image\Box;
use Imagine\Filter\Basic\Thumbnail;
use Imagine\ImageInterface;

class ThumbnailFilterLoader implements LoaderInterface
{
    public function load(array $options = array())
    {
        $mode = $options['mode'] === 'inset' ?
            ImageInterface::THUMBNAIL_INSET :
            ImageInterface::THUMBNAIL_OUTBOUND;

        list($width, $height) = $options['size'];

        return new Thumbnail(new Box($width, $height), $mode);
    }
}
