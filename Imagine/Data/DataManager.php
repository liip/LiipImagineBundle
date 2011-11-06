<?php

namespace Liip\ImagineBundle\Imagine\Data;

use Liip\ImagineBundle\Imagine\Data\Loader\LoaderInterface,
    Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

class DataManager
{
    /**
     * @var FilterConfiguration
     */
    private $filterConfig;

    /**
     * @var string|null
     */
    private $defaultLoader;

    /**
     * @var array
     */
    private $loaders;

    /**
     * @param string $defaultLoader
     * @param array $filters
     */
    public function __construct(FilterConfiguration $filterConfig, $defaultLoader = null)
    {
        $this->filterConfig = $filterConfig;
        $this->defaultLoader = $defaultLoader;
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
        $config = $this->filterConfig->get($filter);

        $loaderName = empty($config['data_loader']) ? $this->defaultLoader : $config['data_loader'];
        if (!isset($this->loaders[$loaderName])) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find data loader for "%s" filter type', $filter
            ));
        }

        return $this->loaders[$loaderName]->find($path);
    }
}
