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
     * @param array  $runtimeConfig
     *
     * @return array
     *
     * @throws \RuntimeException
     */
    public function get($filter, array $runtimeConfig = array())
    {
        if (false == array_key_exists($filter, $this->filters)) {
            throw new \RuntimeException(sprintf('Could not find configuration for a filter: %s', $filter));
        }

        return array_replace_recursive($this->filters[$filter], $runtimeConfig);
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
        $this->filters[$filter] = $config;
    }
}
