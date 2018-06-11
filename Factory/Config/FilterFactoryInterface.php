<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Factory\Config;

use Liip\ImagineBundle\Config\FilterInterface;

interface FilterFactoryInterface
{
    /**
     * Name of the filter that this factory can create
     */
    public function getName(): string;

    public function create(array $options): FilterInterface;
}
