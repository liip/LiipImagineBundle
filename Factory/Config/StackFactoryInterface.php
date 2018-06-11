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
use Liip\ImagineBundle\Config\StackInterface;

interface StackFactoryInterface
{
    /**
     * @param string            $name
     * @param string|null       $dataLoader
     * @param int|null          $quality
     * @param FilterInterface[] $filters
     *
     * @return StackInterface
     */
    public function create(string $name, string $dataLoader = null, int $quality = null, array $filters): StackInterface;
}
