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

/**
 * @codeCoverageIgnore
 */
final class Watermark extends FilterAbstract
{
    const NAME = 'watermark';

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

    public function __construct(string $image, string $position, float $size = null)
    {
        $this->image = $image;
        $this->position = $position;
        $this->size = $size;
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
