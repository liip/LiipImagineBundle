<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Config\Filter\Argument;

/**
 * @codeCoverageIgnore
 */
final class Size
{
    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * To allow keeping aspect ratio, it is allowed to only specify one of width or height.
     * It is however not allowed to specify neither dimension.
     */
    public function __construct(int $width = null, int $height = null)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }
}
