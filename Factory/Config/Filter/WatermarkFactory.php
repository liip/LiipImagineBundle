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

use Liip\ImagineBundle\Config\Filter\Type\Watermark;
use Liip\ImagineBundle\Config\FilterInterface;
use Liip\ImagineBundle\Factory\Config\FilterFactoryInterface;

/**
 * @internal
 * @codeCoverageIgnore
 */
final class WatermarkFactory implements FilterFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return Watermark::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options): FilterInterface
    {
        $size = $options['size'] ?? null;
        if (null !== $size) {
            $size = (float) $size;
        }

        $position = isset($options['position']) ? $options['position'] : 'center';

        return new Watermark($options['image'], $position, $size);
    }
}
