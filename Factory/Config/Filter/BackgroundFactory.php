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

use Liip\ImagineBundle\Config\Filter\Type\Background;
use Liip\ImagineBundle\Config\FilterInterface;
use Liip\ImagineBundle\Factory\Config\FilterFactoryInterface;

final class BackgroundFactory implements FilterFactoryInterface
{
    const NAME = 'background';

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options): FilterInterface
    {
        $color = isset($options['color']) ? $options['color'] : null;
        $transparency = isset($options['transparency']) ? $options['transparency'] : null;
        $position = isset($options['position']) ? $options['position'] : null;
        $size = isset($options['size']) ? $options['size'] : [];

        return new Background(self::NAME, $color, $transparency, $position, $size);
    }
}
