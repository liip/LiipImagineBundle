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
    private $loaders = array();

    /**
     * @param FilterConfiguration $filterConfig
     * @param string $defaultLoader
     */
    public function __construct(FilterConfiguration $filterConfig, $defaultLoader = null)
    {
        $this->filterConfig = $filterConfig;
        $this->defaultLoader = $defaultLoader;
    }

    /**
     * @param string $filter
     * @param LoaderInterface $loader
     * 
     * @return void
     */
    public function addLoader($filter, LoaderInterface $loader)
    {
        $this->loaders[$filter] = $loader;
    }

    /**
     * @param string $filter
     *
     * @return LoaderInterface
     */
    public function getLoader($filter)
    {
        $config = $this->filterConfig->get($filter);

        $loaderName = empty($config['data_loader'])
            ? $this->defaultLoader : $config['data_loader'];

        if (!isset($this->loaders[$loaderName])) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find data loader for "%s" filter type', $filter
            ));
        }

        return $this->loaders[$loaderName];
    }

    /**
     * @param string $filter
     * @param string $path
     *
     * @return Imagine\Image\ImageInterface
     */
    public function find($filter, $path)
    {
        $loader = $this->getLoader($filter);

        return $loader->find($path);
    }
}
