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

use Imagine\Filter\Advanced\Grayscale;
use Imagine\Image\ImageInterface;

/**
 * GrayscaleFilterLoader - apply grayscale filter.
 *
 * @author Gregoire Humeau <gregoire.humeau@gmail.com>
 */
class GrayscaleFilterLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ImageInterface $image, array $options = array())
    {
        $filter = new Grayscale();

        return $filter->apply($image);
    }
}
