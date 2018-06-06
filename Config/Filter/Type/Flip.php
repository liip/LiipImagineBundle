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

final class Flip implements FilterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $axis;

    /**
     * @param string $name
     * @param string $axis possible values are: "x", "horizontal", "y", or "vertical"
     */
    public function __construct(string $name, string $axis)
    {
        $this->name = $name;
        $this->axis = $axis;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAxis(): string
    {
        return $this->axis;
    }
}
