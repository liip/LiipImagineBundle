<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Factory\Config\Filter;

use Liip\ImagineBundle\Config\Filter\Type\Thumbnail;
use Liip\ImagineBundle\Config\FilterInterface;
use Liip\ImagineBundle\Factory\Config\FilterFactoryInterface;

final class ThumbnailFactory implements FilterFactoryInterface
{
    const NAME = 'thumbnail';

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @param array $options
     *
     * @return FilterInterface
     */
    public function create(array $options): FilterInterface
    {
        $size = $options['size'];
        $mode = isset($options['mode']) ? $options['mode'] : null;
        $allowUpscale = isset($options['allow_upscale']) ? $options['allow_upscale'] : null;
        $filter = isset($options['filter']) ? $options['filter'] : null;

        return new Thumbnail(self::NAME, $size, $mode, $allowUpscale, $filter);
    }
}
