<?php

namespace Liip\ImagineBundle\Imagine\Filter;

use Imagine\Filter\FilterInterface;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;

/**
 * Filter for resizing an image, by keeping the aspect ratio and not exceeding the maxWidth and maxHeight parameters
 *
 * @author      Robert-Jan Bijl <r.j.bijl@gmail.com>
 */
class TwoWayRelativeResize implements FilterInterface
{
    /**
     * @var int
     */
    private $newWidth;

    /**
     * @var int
     */
    private $newHeight;

    /**
     * Constructor
     *
     * @param int $newHeight
     * @param int $newWidth
     */
    public function __construct($newHeight, $newWidth)
    {
        $this->newHeight = $newHeight;
        $this->newWidth = $newWidth;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(ImageInterface $image)
    {
        // if we don't have any new dimensions, return the original
        if (null === $this->newHeight && null === $this->newWidth) {
            return $image;
        }

        list($newWidth, $newHeight) = $this->calculateNewDimensions(
            $image->getSize()->getWidth(),
            $image->getSize()->getHeight(),
            $this->newWidth,
            $this->newHeight
        );

        // resize the image accordingly
        return $image->resize(new Box($newWidth, $newHeight));
    }

    /**
     * Calculates new width and height for an image by keeping the aspect ratio,
     * not exceeding the maxWidth and maxHeight parameters
     *
     * @param int $width
     * @param int $height
     * @param int $maxWidth
     * @param int $maxHeight
     * @return array
     */
    private function calculateNewDimensions($width, $height, $maxWidth, $maxHeight)
    {
        $widthFactor = $maxWidth / $width;
        $heightFactor = $maxHeight / $height;

        // if one of the factors is 0, we need the other one.
        if ($widthFactor === 0) {
            $factor = $heightFactor;
        } elseif ($heightFactor === 0) {
            $factor = $widthFactor;
        } else {
            $factor = $widthFactor >= $heightFactor ? $heightFactor : $widthFactor;
        }

        // none of the new dimensions should be 0, so take care of that...
        // this could happen, for example, if we scale done an image with a width of 1 px
        $newWidth = max(array(intval($width * $factor), 1));
        $newHeight = max(array(intval($height * $factor), 1));

        return array($newWidth, $newHeight);
    }
}
