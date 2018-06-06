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

use Liip\ImagineBundle\Config\Filter\Type\RelativeResize;
use Liip\ImagineBundle\Config\FilterInterface;
use Liip\ImagineBundle\Factory\Config\FilterFactoryInterface;

final class RelativeResizeFactory implements FilterFactoryInterface
{
    const NAME = 'relative_resize';

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
        $heighten = isset($options['heighten']) ? $options['heighten'] : null;
        $widen = isset($options['widen']) ? $options['widen'] : null;
        $increase = isset($options['increase']) ? $options['increase'] : null;
        $scale = isset($options['scale']) ? $options['scale'] : null;

        return new RelativeResize(self::NAME, $heighten, $widen, $increase, $scale);
    }
}
