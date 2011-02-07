<?php

namespace Bundle\Avalanche\ImagineBundle\Imagine;

use Imagine\Exception\InvalidArgumentException;
use Imagine\Filter\FilterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FilterManager
{
    private $services;
    private $container;

    public function __construct(ContainerInterface $container, array $services = array())
    {
        $this->container = $container;
        $this->services  = $services;
    }

    public function get($filter)
    {
        if (!array_key_exists($filter, $this->services)) {
            throw new InvalidArgumentException(sprintf('Unknown filter "%s"',
                $filter));
        }

        $filter = $this->container->get($this->services[$filter]);

        if (!$filter instanceof FilterInterface) {
            throw new InvalidArgumentException(sprintf('Filter "%s" does not '.
            'implement Imagine\Filter\FilterInterface', $filter));
        }

        return $filter;
    }
}
