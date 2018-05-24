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

use Liip\ImagineBundle\Config\FilterSet;

class FilterSetFactory
{
    /**
     * @param string $name
     * @param string $dataLoader
     * @param int $quality
     * @param array $filters
     * @return FilterSet
     */
    public function create(string $name, string $dataLoader, int $quality, array $filters)
    {
        return new FilterSet($name, $dataLoader, $quality, $filters);
    }
}
