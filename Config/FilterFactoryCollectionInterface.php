<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Config;

use Liip\ImagineBundle\Factory\Config\FilterFactoryInterface;

interface FilterFactoryCollectionInterface
{
    /**
     * @param string $name
     *
     * @return FilterFactoryInterface
     */
    public function getFilterFactoryByName(string $name): FilterFactoryInterface;

    /**
     * @return FilterFactoryInterface[]
     */
    public function getAll();
}
