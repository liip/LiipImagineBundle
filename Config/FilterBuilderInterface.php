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

interface FilterBuilderInterface
{
    /**
     * @param string $filterName
     * @param array  $filterData
     *
     * @return FilterInterface
     */
    public function build(string $filterName, array $filterData): FilterInterface;
}
