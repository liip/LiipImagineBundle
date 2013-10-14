<?php

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Image\ImageInterface;

class AutoRotateFilterLoader implements LoaderInterface
{
    /**
     * {@inheritDoc}
     */
    function load(ImageInterface $image, array $options = array())
    {
        $exifData = exif_read_data("data://image/jpeg;base64," . base64_encode($image->get('jpg')));

        if (isset($exifData['Orientation'])) {
            $orientation = (int)$exifData['Orientation'];

            $degree = 0;
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
            }

            if ($degree !== 0) {
                $image->rotate($degree);
            }
        }

        return $image;
    }
}