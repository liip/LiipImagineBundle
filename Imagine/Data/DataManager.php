<?php

namespace Liip\ImagineBundle\Imagine\Data;

use Liip\ImagineBundle\Imagine\Data\Loader\LoaderInterface;

class DataManager
{
    /**
     * @var string|null
     */
    private $defaultLoader;

    /**
     * @var array
     */
    private $filters;

    /**
     * @var array
     */
    private $loaders;

    /**
     * @param string $defaultLoader
     * @param array $filters
     */
    public function __construct($defaultLoader = null, array $filters = array())
    {
        $this->defaultLoader = $defaultLoader;
        $this->filters = $filters;
        $this->loaders = array();
    }

    /**
     * @param $name
     * @param LoaderInterface $loader
     * 
     * @return void
     */
    public function addLoader($name, LoaderInterface $loader)
    {
        $this->loaders[$name] = $loader;
    }

    /**
     * @param $filter
     * @param string $path
     *
     * @return Imagine\Image\ImageInterface
     */
    public function find($filter, $path)
    {
        if (!isset($this->filters[$filter])) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find image filter "%s"', $filter
            ));
        }

        $config = $this->filters[$filter];

        $loaderName = empty($config['data_loader']) ? $this->defaultLoader : $config['data_loader'];
        if (!isset($this->loaders[$loaderName])) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find data loader for "%s" filter type', $filter
            ));
        }

        return $this->loaders[$loaderName]->find($path);
    }
}
