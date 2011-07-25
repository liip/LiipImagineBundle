<?php

namespace Avalanche\Bundle\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Image\Box;
use Imagine\Image\ManipulatorInterface;
use Imagine\Filter\Basic\Thumbnail;

class ThumbnailFilterLoader implements LoaderInterface
{
    public function load(array $options = array())
    {
        $mode = $options['mode'] === 'inset' ?
            ManipulatorInterface::THUMBNAIL_INSET :
            ManipulatorInterface::THUMBNAIL_OUTBOUND;

        list($width, $height) = $options['size'];

        return new Thumbnail(new Box($width, $height), $mode);
    }
}
