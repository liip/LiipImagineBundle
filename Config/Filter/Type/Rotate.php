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

final class Rotate implements FilterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $angle;

    /**
     * @param string $name
     */
    public function __construct(string $name, int $angle)
    {
        $this->name = $name;
        $this->angle = $angle;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getAngle(): int
    {
        return $this->angle;
    }
}
