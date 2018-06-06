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

final class Scale implements FilterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $dimensions = [];

    /**
     * @var float
     */
    private $to;

    /**
     * @param string     $name
     * @param array      $dimensions array containing a width and height integer
     * @param float|null $to         proportional scale operation computed by multiplying all image sides by this value
     */
    public function __construct(string $name, array $dimensions, float $to = null)
    {
        $this->name = $name;
        $this->dimensions = $dimensions;
        $this->to = $to;
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
    public function getDimensions(): array
    {
        return $this->dimensions;
    }

    /**
     * @return float
     */
    public function getTo()
    {
        return $this->to;
    }
}
