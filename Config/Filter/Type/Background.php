<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Config\Filter\Type;

use Liip\ImagineBundle\Config\Filter\Argument\Size;

/**
 * @codeCoverageIgnore
 */
final class Background extends FilterAbstract
{
    const NAME = 'background';

    /**
     * @var string
     */
    private $color;

    /**
     * @var string
     */
    private $transparency;

    /**
     * @var string
     */
    private $position;

    /**
     * @var Size
     */
    private $size;

    /**
     * @param string|null $color        background color HEX value
     * @param string|null $transparency possible values 0..100
     * @param string|null $position     position of the input image on the newly created background image. Valid values: topleft, top, topright, left, center, right, bottomleft, bottom, and bottomright
     */
    public function __construct(
        string $color = null,
        string $transparency = null,
        string $position = null,
        Size $size
    ) {
        $this->color = $color;
        $this->transparency = $transparency;
        $this->position = $position;
        $this->size = $size;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function getTransparency(): ?string
    {
        return $this->transparency;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function getSize(): Size
    {
        return $this->size;
    }
}
