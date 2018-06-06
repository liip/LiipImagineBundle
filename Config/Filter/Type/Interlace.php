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

final class Interlace implements FilterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $mode;

    /**
     * @param string $name
     * @param string $mode
     */
    public function __construct(string $name, string $mode)
    {
        $this->name = $name;
        $this->mode = $mode;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMode(): string
    {
        return $this->mode;
    }
}
