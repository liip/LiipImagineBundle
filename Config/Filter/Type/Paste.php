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

final class Paste implements FilterInterface
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
     * @param string $name
     * @param array  $start
     */
    public function __construct(string $name, array $start = [])
    {
        $this->name = $name;
        $this->start = $start;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStart(): array
    {
        return $this->start;
    }
}
