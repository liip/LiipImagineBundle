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

final class Watermark implements FilterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $image;

    /**
     * @var string
     */
    private $position;

    /**
     * @var float
     */
    private $size;

    /**
     * @param string     $name
     * @param string     $image
     * @param string     $position
     * @param float|null $size
     */
    public function __construct(string $name, string $image, string $position, float $size = null)
    {
        $this->name = $name;
        $this->image = $image;
        $this->position = $position;
        $this->size = $size;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function getSize(): ?float
    {
        return $this->size;
    }
}
