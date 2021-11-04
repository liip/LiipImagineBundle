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

use Imagine\Filter\Basic\Resize;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;

/**
 * Loader for Imagine's basic resize filter.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class ResizeFilterLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ImageInterface $image, array $options = [])
    {
        $width = isset($options['size'][0]) ? $options['size'][0] : null;
        $height = isset($options['size'][1]) ? $options['size'][1] : null;

        $filter = new Resize(new Box($width, $height));

        return $filter->apply($image);
    }
}
