<?php

namespace Liip\ImagineBundle\Imagine\Filter;

use Liip\ImagineBundle\Exception\Imagine\Filter\NonExistingFilterException;

class FilterConfiguration
{
    /**
     * @var array
     */
    protected $filters = array();

    /**
     * @param array $filters
     */
    public function __construct(array $filters = array())
    {
        $this->filters = $filters;
    }

    /**
     * Gets a previously configured filter.
     *
     * @param string $filter
     *
     * @return array
     *
     * @throws NonExistingFilterException
     */
    public function get($filter)
    {
        if (false === array_key_exists($filter, $this->filters)) {
            throw new NonExistingFilterException(sprintf('Could not find configuration for a filter: %s', $filter));
        }

        return $this->filters[$filter];
    }

    /**
     * Sets a configuration on the given filter.
     *
     * @param string $filter
     * @param array  $config
     *
     * @return array
     */
    public function set($filter, array $config)
    {
        $this->filters[$filter] = $config;
    }

    /**
     * Get all filters.
     *
     * @return array
     */
    public function all()
    {
        return $this->filters;
    }
}
