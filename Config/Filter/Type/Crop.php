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

final class Crop implements FilterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $start;

    /**
     * @var array
     */
    private $size;

    /**
     * @param string $name
     * @param array  $start
     * @param array  $size
     */
    public function __construct(string $name, array $start, array $size)
    {
        $this->name = $name;
        $this->start = $start;
        $this->size = $size;
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
    public function getStart(): array
    {
        return $this->start;
    }

    /**
     * @return array
     */
    public function getSize(): array
    {
        return $this->size;
    }
}
