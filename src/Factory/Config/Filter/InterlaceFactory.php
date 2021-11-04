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

/**
 * @internal
 * @codeCoverageIgnore
 */
final class InterlaceFactory implements FilterFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return Interlace::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options): FilterInterface
    {
        $mode = $options['mode'] ?? ImageInterface::INTERLACE_LINE;

        return new Interlace($mode);
    }
}
