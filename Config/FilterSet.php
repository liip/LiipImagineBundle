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

use Liip\ImagineBundle\Exception\InvalidArgumentException;

final class FilterSet implements FilterSetInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $dataLoader;

    /**
     * @var int
     */
    private $quality;

    /**
     * @var FilterInterface[]
     */
    private $filters = [];

    /**
     * @param string $name
     * @param string $dataLoader
     * @param int $quality
     * @param FilterInterface[] $filters
     */
    public function __construct(string $name, string $dataLoader, int $quality, array $filters)
    {
        $this->name = $name;
        $this->dataLoader = $dataLoader;
        $this->quality = $quality;
        $this->setFilters($filters);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDataLoader(): string
    {
        return $this->dataLoader;
    }

    /**
     * @return int
     */
    public function getQuality(): int
    {
        return $this->quality;
    }

    /**
     * @return FilterInterface[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param FilterInterface[] $filters
     */
    private function setFilters(array $filters): void
    {
        foreach ($filters as $filter) {
            if (!($filter instanceof FilterInterface)) {
                throw new InvalidArgumentException('Unknown filter provided.');
            }
        }
        $this->filters = $filters;
    }
}
