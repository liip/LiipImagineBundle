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
 * Loader for Imagine's basic rotate method.
 *
 * @author Bocharsky Victor <bocharsky.bw@gmail.com>
 */
class RotateFilterLoader implements LoaderInterface
{
    public function load(ImageInterface $image, array $options = []): ImageInterface
    {
        $angle = isset($options['angle']) ? (int) $options['angle'] : 0;

        return 0 === $angle ? $image : $image->rotate($angle);
    }
}
