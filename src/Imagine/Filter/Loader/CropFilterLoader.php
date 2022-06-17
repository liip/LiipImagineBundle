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

use Imagine\Filter\Basic\Crop;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;

class CropFilterLoader implements LoaderInterface
{
    public function load(ImageInterface $image, array $options = []): ImageInterface
    {
        $x = $options['start'][0] ?? null;
        $y = $options['start'][1] ?? null;

        $width = $options['size'][0] ?? null;
        $height = $options['size'][1] ?? null;

        $filter = new Crop(new Point($x, $y), new Box($width, $height));

        return $filter->apply($image);
    }
}
