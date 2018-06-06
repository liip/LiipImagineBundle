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
use Liip\ImagineBundle\Config\FilterInterface;

final class Background implements FilterInterface
{
    /**
     * @var string
     */
    private $name;

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
     * @param string      $name
     * @param string|null $color
     * @param string|null $transparency
     * @param string|null $position
     * @param Size        $size
     */
    public function __construct(
        string $name,
        string $color = null,
        string $transparency = null,
        string $position = null,
        Size $size
    ) {
        $this->name = $name;
        $this->color = $color;
        $this->transparency = $transparency;
        $this->position = $position;
        $this->size = $size;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getTransparency(): string
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
