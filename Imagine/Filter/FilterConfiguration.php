<?php

namespace Liip\ImagineBundle\Imagine\Filter;

class FilterConfiguration
{
    /**
     * @var array
     */
    private $filters;

    /**
     * @param array $filters
     */
    public function __construct(array $filters = array())
    {
        $this->filters = $filters;
    }

    /**
     * @param $filter
     *
     * @return array
     */
    public function get($filter)
    {
        if (empty($this->filters[$filter])) {
            new \RuntimeException('Filter not defined: '.$filter);
        }

        return $this->filters[$filter];
    }

    /**
     * @param $filter
     *
     * @return array
     */
    public function set($filter, $config)
    {
        return $this->filters[$filter] = $config;
    }
}
