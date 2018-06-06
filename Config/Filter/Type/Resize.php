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

final class Resize implements FilterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $size = [];

    /**
     * @param string $name
     * @param array  $size size parameters {width, height}
     */
    public function __construct(string $name, array $size)
    {
        $this->name = $name;
        $this->size = $size;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSize(): array
    {
        return $this->size;
    }
}
