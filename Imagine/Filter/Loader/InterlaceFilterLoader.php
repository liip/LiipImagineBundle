<?php

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Image\ImageInterface;

class InterlaceFilterLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ImageInterface $image, array $options = array())
    {
        $mode = ImageInterface::INTERLACE_LINE;
        if (!empty($options['mode'])) {
            $mode = $options['mode'];
        }

        $image->interlace($mode);

        return $image;
    }
}
