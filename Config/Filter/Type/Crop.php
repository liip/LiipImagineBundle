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

use Liip\ImagineBundle\Config\Filter\Argument\Point;
use Liip\ImagineBundle\Config\Filter\Argument\Size;
use Liip\ImagineBundle\Config\FilterInterface;

final class Crop implements FilterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Point
     */
    private $startPoint;

    /**
     * @var Size
     */
    private $size;

    public function __construct(string $name, Point $startPoint, Size $size)
    {
        $this->name = $name;
        $this->startPoint = $startPoint;
        $this->size = $size;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStartPoint(): Point
    {
        return $this->startPoint;
    }

    public function getSize(): Size
    {
        return $this->size;
    }
}
