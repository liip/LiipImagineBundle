<?php

namespace Liip\ImagineBundle\Model\Filter;

use Imagine\Filter\FilterInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Data\Loader\LoaderInterface;

class Configuration
{
    protected $id;
    protected $filter;
    protected $options;
    protected $loader;
    protected $resolver;

    public function __construct($id, FilterInterface $filter, Options $options, LoaderInterface $loader, ResolverInterface $resolver)
    {
        $this->id = $id;
        $this->filter = $filter;
        $this->options = $options;
        $this->loader = $loader;
        $this->resolver = $resolver;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getLoader()
    {
        return $this->loader;
    }

    public function getResolver()
    {
        return $this->resolver;
    }
}
