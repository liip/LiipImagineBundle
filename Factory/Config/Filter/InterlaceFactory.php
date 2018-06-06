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

use Imagine\Image\ImageInterface;
use Liip\ImagineBundle\Config\Filter\Type\Interlace;
use Liip\ImagineBundle\Config\FilterInterface;
use Liip\ImagineBundle\Factory\Config\FilterFactoryInterface;

final class InterlaceFactory implements FilterFactoryInterface
{
    const NAME = 'interlace';

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
        $mode = $options['mode'] ?? ImageInterface::INTERLACE_LINE;

        return new Interlace(self::NAME, $mode);
    }
}
