<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Filter;

use Liip\ImagineBundle\Exception\Imagine\Filter\NonExistingFilterException;

class FilterConfiguration
{
    protected array $filters = [];

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Gets a previously configured filter.
     *
     * @throws NonExistingFilterException
     */
    public function get(string $filter): array
    {
        if (false === \array_key_exists($filter, $this->filters)) {
            throw new NonExistingFilterException(sprintf('Could not find configuration for a filter: %s', $filter));
        }

        return $this->filters[$filter];
    }

    /**
     * Sets a configuration on the given filter.
     */
    public function set(string $filter, array $config): void
    {
        $this->filters[$filter] = $config;
    }

    /**
     * Get all filters.
     */
    public function all(): array
    {
        return $this->filters;
    }
}
