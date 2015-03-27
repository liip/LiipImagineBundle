<?php

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;

class BackgroundFilterLoader implements LoaderInterface
{
    public function __construct(ImagineInterface $imagine)
    {
        $this->imagine = $imagine;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ImageInterface $image, array $options = array())
    {
        $background = $image->palette()->color(
            isset($options['color']) ? $options['color'] : '#fff',
            isset($options['transparency']) ? $options['transparency'] : null
        );
        $topLeft = new Point(0, 0);
        $size = $image->getSize();

        if (isset($options['size'])) {
            list($width, $height) = $options['size'];

            $size = new Box($width, $height);
            $topLeft = new Point(($width - $image->getSize()->getWidth()) / 2, ($height - $image->getSize()->getHeight()) / 2);
        }

        $canvas = $this->imagine->create($size, $background);

        return $canvas->paste($image, $topLeft);
    }
}
