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

final class Upscale implements FilterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $min;

    /**
     * @var float
     */
    private $by;

    /**
     * @param string $name
     * @param array  $min  desired min dimensions {width, height}
     * @param float  $by   sets the "ratio multiple" which initiates a proportional scale operation computed by multiplying all image sides by this value
     */
    public function __construct(string $name, array $min = [], float $by = null)
    {
        $this->name = $name;
        $this->min = $min;
        $this->by = $by;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getMin(): array
    {
        return $this->min;
    }

    /**
     * @return float|null
     */
    public function getBy()
    {
        return $this->by;
    }
}
