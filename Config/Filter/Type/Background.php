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
     * @var array
     */
    private $size;

    /**
     * @param string      $name
     * @param string|null $color
     * @param string|null $transparency
     * @param string|null $position
     * @param array       $size
     */
    public function __construct(
        string $name,
        string $color = null,
        string $transparency = null,
        string $position = null,
        array $size = []
    ) {
        $this->name = $name;
        $this->color = $color;
        $this->transparency = $transparency;
        $this->position = $position;
        $this->size = $size;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @return string|null
     */
    public function getTransparency(): string
    {
        return $this->transparency;
    }

    /**
     * @return string|null
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return array
     */
    public function getSize(): array
    {
        return $this->size;
    }
}
