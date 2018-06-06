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

final class Downscale implements FilterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $max;

    /**
     * @var float
     */
    private $by;

    /**
     * @param string $name
     * @param array  $max  desired max dimensions {width, height}
     * @param float  $by   sets the "ratio multiple" which initiates a proportional scale operation computed by multiplying all image sides by this value
     */
    public function __construct(string $name, array $max = [], float $by = null)
    {
        $this->name = $name;
        $this->max = $max;
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
    public function getMax(): array
    {
        return $this->max;
    }

    /**
     * @return float|null
     */
    public function getBy()
    {
        return $this->by;
    }
}
