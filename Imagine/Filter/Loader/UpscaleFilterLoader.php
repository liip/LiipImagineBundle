<?php

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Filter\Basic\Resize;
use Imagine\Image\ImageInterface;
use Imagine\Image\Box;

/**
 * Upscale filter.
 *
 * @author Maxime Colin <contact@maximecolin.fr>
 */
class UpscaleFilterLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ImageInterface $image, array $options = array())
    {
        if (!isset($options['min'])) {
            throw new \InvalidArgumentException('Missing min option.');
        }

        list($width, $height) = $options['min'];

        $size = $image->getSize();
        $origWidth = $size->getWidth();
        $origHeight = $size->getHeight();

        if ($origWidth < $width || $origHeight < $height) {
            $widthRatio = $width / $origWidth;
            $heightRatio = $height / $origHeight;

            // faster check than is_null
            if ($width === null || $height === null) {
                $ratio = max($widthRatio, $heightRatio);
            } else {
                $ratio = min($widthRatio, $heightRatio);
            }

            $filter = new Resize(new Box(round($origWidth * $ratio), round($origHeight * $ratio)));

            return $filter->apply($image);
        }

        return $image;
    }
}
