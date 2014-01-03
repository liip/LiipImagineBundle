<?php

namespace Liip\ImagineBundle\Controller;

use Imagine\Filter\ImagineAware;
use Imagine\Image\ImagineInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Model\Filter\ConfigurationCollection;
use Liip\ImagineBundle\Model\Filter\ConfigurableFilterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ImagineController
{
    /**
     * @var ConfigurationCollection
     */
    protected $configurations;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var ImagineInterface
     */
    protected $imagine;

    /**
     * Constructor.
     *
     * @param DataManager             $dataManager
     * @param ConfigurationCollection $configurations
     * @param CacheManager            $cacheManager
     * @param ImagineInterface        $imagine
     */
    public function __construct(DataManager $dataManager, ConfigurationCollection $configurations, CacheManager $cacheManager, ImagineInterface $imagine)
    {
        $this->dataManager = $dataManager;
        $this->configurations = $configurations;
        $this->cacheManager = $cacheManager;
        $this->imagine = $imagine;
    }

    /**
     * This action applies a given filter to a given image, optionally saves the image and outputs it to the browser at the same time.
     *
     * @param string $path
     * @param string $filterset
     *
     * @return Response
     */
    public function filterAction($path, $filterset)
    {
        if ($this->cacheManager->isStored($path, $filterset)) {
            return new RedirectResponse($this->cacheManager->resolve($path, $filterset), 301);
        }

        $configuration = $this->configurations->getConfiguration($filterset);

        $filter = $configuration->getFilter();
        if ($filter instanceof ImagineAware) {
            $filter->setImagine($this->imagine);
        }

        // Apply the filter options (again), as the same filter object may be used within different configurations with different options.
        if ($filter instanceof ConfigurableFilterInterface) {
            $filter->configure($configuration->getOptions());
        }

        $rawImage = $this->dataManager->find($filterset, $path);
        $image = $filter->apply($this->imagine->load($rawImage->getContent()));

        // TODO: Set format dynamicly.
        $response = new Response($image->get('jpg'), 200, 'image/jpeg');

        return $this->cacheManager->store($response, $path, $filterset);
    }
}
