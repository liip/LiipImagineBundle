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
        foreach ($filters as $filter => $config) {
            $this->set($filter, $config);
        }
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

        $defaultConfig = $this->filters[$filter];
        $config = array_replace_recursive($defaultConfig, $runtimeConfig);

        // $defaultConfig['format'] means we always want to format an image to this format.
        // So this adds BC with previous versions.
        if ($defaultConfig['format']) {
            $config['format'] = $defaultConfig['format'];
        }
        if (empty($config['format'])) {
            $config['format'] = 'png';
        }

        return $config;
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
        $this->filters[$filter] = array_replace(
            array(
                'quality' => 100,
                'format' => null,
                'filters' => array(),
            ),
            $config
        );
    }
}
