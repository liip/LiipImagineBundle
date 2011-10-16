<?php

namespace Liip\ImagineBundle\Imagine\Filter;

use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

use Imagine\Exception\InvalidArgumentException;

class FilterManager
{
    private $filters;
    private $loaders;
    private $services;

    public function __construct(array $filters = array())
    {
        $this->filters = $filters;
        $this->loaders = array();
        $this->services = array();
    }

    public function addLoader($name, LoaderInterface $loader)
    {
        $this->loaders[$name] = $loader;
    }

    public function getFilterConfig($filter)
    {
        if (empty($this->filters[$filter])) {
            new \RuntimeException('Filter not defined: '.$filter);
        }

        return $this->filters[$filter];
    }

    public function get($filter, $image, $format = 'png')
    {
        if (!isset($this->filters[$filter])) {
            throw new InvalidArgumentException(sprintf(
                'Could not find image filter "%s"', $filter
            ));
        }

        $config = $this->filters[$filter];

        if (isset($config['type'])) {
            if (!isset($this->loaders[$config['type']])) {
                throw new InvalidArgumentException(sprintf(
                    'Could not find loader for "%s" filter type', $config['type']
                ));
            }

            if (!isset($config['options'])) {
                throw new InvalidArgumentException(sprintf(
                    'Options for filter type "%s" must be specified', $filter
                ));
            }

            $image = $this->loaders[$config['type']]->load($image, $config['options']);
        }

        $quality = empty($config['quality']) ? 100 : $config['quality'];
        $image = $image->get($format, array('quality' => $quality));

        return $image;
    }
}
