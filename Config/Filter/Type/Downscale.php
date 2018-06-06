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

final class Downscale implements FilterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Size
     */
    private $max;

    /**
     * @var float
     */
    private $by;

    /**
     * @param string     $name
     * @param Size       $max
     * @param float|null $by   sets the "ratio multiple" which initiates a proportional scale operation computed by multiplying all image sides by this value
     */
    public function __construct(string $name, Size $max, float $by = null)
    {
        $this->name = $name;
        $this->max = $max;
        $this->by = $by;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMax(): Size
    {
        return $this->max;
    }

    public function getBy(): ?float
    {
        return $this->by;
    }
}
