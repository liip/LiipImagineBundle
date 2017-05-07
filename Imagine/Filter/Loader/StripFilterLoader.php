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

use Imagine\Filter\Basic\Strip;
use Imagine\Image\ImageInterface;

class StripFilterLoader implements LoaderInterface
{
    public function load(ImageInterface $image, array $options = [])
    {
        $filter = new Strip();
        $image = $filter->apply($image);

        return $image;
    }
}
