<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Domain\Imagine\Filter;

use Imagine\Image\ImageInterface;

interface FilterExecutor
{
    /**
     * Loads and applies a filter on the given image.
     */
    public function applyTo(ImageInterface $image, array $options = []): ImageInterface;
}
