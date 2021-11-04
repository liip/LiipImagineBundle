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

interface StackInterface
{
    public function getName(): string;

    public function getDataLoader(): ?string;

    public function getQuality(): ?int;

    /**
     * @return FilterInterface[]
     */
    public function getFilters(): array;
}
