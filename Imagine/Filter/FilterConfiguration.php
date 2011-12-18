<?php

namespace Liip\ImagineBundle\Imagine\Filter;

use Symfony\Component\HttpFoundation\Request;

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
     * @param string $filter
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
     * @param string $filter
     * @param array $config
     *
     * @return array
     */
    public function set($filter, array $config)
    {
        return $this->filters[$filter] = $config;
    }

    /**
     * Update filter configuration with values from Request object
     * 
     * @param string $filter
     * @param Request $request
     *
     * @return array
     */
    public function updateFromRequest($filter, Request $request)
    {
        $filterConfiguration = $this->get($filter);
        
        if (!empty($filterConfiguration['route']))
        {
            array_walk_recursive($filterConfiguration['filters'], function(&$item, $key) use ($request)
            {
              if ($item !== ($lookupItem = str_replace('$', '', $item)) && $request->get($lookupItem))
              {
                $item = $request->get($lookupItem);
              }
            });

            $filterConfiguration = $this->set($filter, $filterConfiguration);
        }
        
        return $filterConfiguration;
    }

    /**
     * Verify if provided access key (hash) is valid?
     * 
     * @param string $filter
     * @param string $path
     * @param Request $request
     *
     * @return boolean
     */
    public function isValidAccessKey($filter, $path, Request $request) {
        
        $filterConfiguration = $this->get($filter);
        
        if (!isset($filterConfiguration["route"]["hash"]) || false === $filterConfiguration["route"]["hash"]) {
            return true;
        }
        
        $validHash = substr(md5($request->get('width') . "|" . $request->get('height') . "|" . $path), 0, 4);
        
        return $request->get('hash') == $validHash;
        
    }
}
