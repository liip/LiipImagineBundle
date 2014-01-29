<?php

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Image\ImageInterface;

/**
 * AutoRotateFilterLoader - rotates an Image based on its EXIF Data
 *
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class AutoRotateFilterLoader implements LoaderInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ImageInterface $image, array $options = array())
    {
        $exifData = exif_read_data("data://image/jpeg;base64," . base64_encode($image->get('jpg')));

        if (isset($exifData['Orientation'])) {
            $orientation = (int) $exifData['Orientation'];
            $degree = $this->calculateRotation($orientation);

            if ($degree !== 0) {
                $image->rotate($degree);
            }
        }

        return $image;
    }

    /**
     * calculates to rotation degree from the EXIF Orientation
     *
     * @param  int $orientation
     * @return int
     */
    private function calculateRotation($orientation)
    {
        switch ($orientation) {
            case 8:
                $degree = -90;
                break;
            case 3:
                $degree = 180;
                break;
            case 6:
                $degree = 90;
                break;
            default:
                $degree = 0;
                break;
        }

        return $degree;
    }
}
