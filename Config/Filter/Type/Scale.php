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

final class Scale implements FilterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Size
     */
    private $dimensions;

    /**
     * @var float
     */
    private $to;

    /**
     * @param string     $name
     * @param Size       $dimensions
     * @param float|null $to         proportional scale operation computed by multiplying all image sides by this value
     */
    public function __construct(string $name, Size $dimensions, float $to = null)
    {
        $this->name = $name;
        $this->dimensions = $dimensions;
        $this->to = $to;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDimensions(): Size
    {
        return $this->dimensions;
    }

    public function getTo(): ?float
    {
        return $this->to;
    }
}
