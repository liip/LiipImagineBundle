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

interface FilterSetInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getDataLoader(): string;

    /**
     * @return int
     */
    public function getQuality(): int;

    /**
     * @return FilterInterface[]
     */
    public function getFilters(): array;

    /**
     * @param string $name
     */
    public function setName(string $name): void;

    /**
     * @param string $dataLoader
     */
    public function setDataLoader($dataLoader): void;

    /**
     * @param int $quality
     */
    public function setQuality(int $quality): void;

    /**
     * @param array $filters
     */
    public function setFilters(array $filters): void;
}
