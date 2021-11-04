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

final class Stack implements StackInterface
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
     * @param string|null       $dataLoader name of a custom data loader. Default value: filesystem (which means the standard filesystem loader is used).
     * @param FilterInterface[] $filters
     */
    public function __construct(string $name, string $dataLoader = null, int $quality = null, array $filters)
    {
        $this->name = $name;
        $this->dataLoader = $dataLoader;
        $this->quality = $quality;
        $this->setFilters($filters);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDataLoader(): ?string
    {
        return $this->dataLoader;
    }

    public function getQuality(): ?int
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
