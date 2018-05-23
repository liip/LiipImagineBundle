<?php
/**
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
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string|null $dataLoader
     */
    public function setDataLoader($dataLoader): void
    {
        $this->dataLoader = (string)$dataLoader;
    }

    /**
     * @param int $quality
     */
    public function setQuality(int $quality): void
    {
        $this->quality = $quality;
    }

    /**
     * @param FilterInterface[] $filters
     */
    public function setFilters(array $filters): void
    {
        foreach ($filters as $filter) {
            if (!($filter instanceof FilterInterface)) {
                throw new InvalidArgumentException('Unknown filter provided.');
            }
        }
        $this->filters = $filters;
    }
}
