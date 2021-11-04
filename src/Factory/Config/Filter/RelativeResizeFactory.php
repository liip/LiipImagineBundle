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

/**
 * @internal
 * @codeCoverageIgnore
 */
final class RelativeResizeFactory implements FilterFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return RelativeResize::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options): FilterInterface
    {
        $heighten = $options['heighten'] ?? null;
        $widen = $options['widen'] ?? null;
        $increase = $options['increase'] ?? null;
        $scale = $options['scale'] ?? null;

        return new RelativeResize($heighten, $widen, $increase, $scale);
    }
}
