<?php

namespace Avalanche\Bundle\ImagineBundle\Imagine\Filter;

use Avalanche\Bundle\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

use Imagine\Exception\InvalidArgumentException;
use Imagine\Filter\FilterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FilterManager
{
    private $filters;
    private $loaders;
    private $services;

    public function __construct( array $filters = array())
    {
        $this->filters   = $filters;
        $this->loaders   = array();
        $this->services  = array();
    }

    public function addLoader($name, LoaderInterface $loader)
    {
        $this->loaders[$name] = $loader;
    }

    public function get($filter)
    {
        if (!isset($this->filters[$filter])) {
            throw new InvalidArgumentException(sprintf(
                'Could not find image filter "%s"', $filter
            ));
        }

        $options = $this->filters[$filter];

        if (!isset($options['type'])) {
            throw new InvalidArgumentException(sprintf(
                'Filter type for "%s" image filter must be specified', $filter
            ));
        }

        if (!isset($this->loaders[$options['type']])) {
            throw new InvalidArgumentException(sprintf(
                'Could not find loader for "%s" filter type', $options['type']
            ));
        }

        if (!isset($options['options'])) {
            throw new InvalidArgumentException(sprintf(
                'Options for filter type "%s" must be specified', $filter
            ));
        }

        return $this->loaders[$options['type']]->load($options['options']);
    }
}
