<?php

namespace Liip\ImagineBundle\Imagine\Filter;

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
     * @throws \RuntimeException
     */
    public function get($filter)
    {
        if (empty($this->filters[$filter])) {
            throw new \RuntimeException('Filter not defined: '.$filter);
        }

        return $this->filters[$filter];
    }

    /**
     * Sets a configuration on the given filter.
     *
     * @param string $filter
     * @param array $config
     *
     * @return array
     */
    public function set($filter, array $config)
    {
        return $this->filters[$filter] = $config;
    }
}
