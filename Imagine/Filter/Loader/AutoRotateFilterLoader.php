<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Image\ImageInterface;

/**
 * AutoRotateFilterLoader - rotates an Image based on its EXIF Data.
 *
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class AutoRotateFilterLoader implements LoaderInterface
{
    protected $orientationKeys = [
        'exif.Orientation',
        'ifd0.Orientation',
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ImageInterface $image, array $options = [])
    {
        if (null !== $orientation = $this->getOrientation($image)) {
            if ($orientation < 1 || $orientation > 8) {
                return $image;
            }

            // Rotates if necessary.
            $degree = $this->calculateRotation($orientation);
            if (0 !== $degree) {
                $image->rotate($degree);
            }

            // Flips if necessary.
            if ($this->isFlipped($orientation)) {
                $image->flipHorizontally();
            }
        }

        return $image;
    }

    /**
     * calculates to rotation degree from the EXIF Orientation.
     *
     * @param int $orientation
     *
     * @return int
     */
    private function calculateRotation($orientation)
    {
        switch ($orientation) {
            case 1:
            case 2:
                return 0;
            case 3:
            case 4:
                return 180;
            case 5:
            case 6:
                return 90;
            case 7:
            case 8:
                return -90;
        }
    }

    /**
     * @param ImageInterface $image
     *
     * @return int|null
     */
    private function getOrientation(ImageInterface $image)
    {
        foreach ($this->orientationKeys as $orientationKey) {
            $orientation = $image->metadata()->offsetGet($orientationKey);

            if ($orientation) {
                $image->metadata()->offsetSet($orientationKey, '1');

                return (int) $orientation;
            }
        }

        return null;
    }

    /**
     * Returns true if the image is flipped, false otherwise.
     *
     * @param int $orientation
     *
     * @return bool
     */
    private function isFlipped($orientation)
    {
        return \in_array((int) $orientation, [2, 4, 5, 7], true);
    }
}
