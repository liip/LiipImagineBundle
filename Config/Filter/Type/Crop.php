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

/**
 * @codeCoverageIgnore
 */
final class Crop extends FilterAbstract
{
    const NAME = 'crop';

    /**
     * @var Point
     */
    private $startPoint;

    /**
     * @var Size
     */
    private $size;

    public function __construct(Point $startPoint, Size $size)
    {
        $this->startPoint = $startPoint;
        $this->size = $size;
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
