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

final class Upscale implements FilterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Size
     */
    private $min;

    /**
     * @var float
     */
    private $by;

    /**
     * @param string $name
     * @param Size   $min
     * @param float  $by   sets the "ratio multiple" which initiates a proportional scale operation computed by multiplying all image sides by this value
     */
    public function __construct(string $name, Size $min, float $by = null)
    {
        $this->name = $name;
        $this->min = $min;
        $this->by = $by;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMin(): Size
    {
        return $this->min;
    }

    public function getBy(): ?float
    {
        return $this->by;
    }
}
