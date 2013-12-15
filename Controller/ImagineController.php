<?php

namespace Liip\ImagineBundle\Controller;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ImagineController
{
    /**
     * @var DataManager
     */
    protected $dataManager;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * Constructor.
     *
     * @param DataManager $dataManager
     * @param FilterManager $filterManager
     * @param CacheManager $cacheManager
     */
    public function __construct(DataManager $dataManager, FilterManager $filterManager, CacheManager $cacheManager)
    {
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
        $this->cacheManager = $cacheManager;
    }

    /**
     * This action applies a given filter to a given image, optionally saves the image and outputs it to the browser at the same time.
     *
     * @param Request $request
     * @param string $path
     * @param string $filter
     *
     * @return Response
     */
    public function filterAction(Request $request, $path, $filter)
    {
        if ($this->cacheManager->isStored($path, $filter)) {
            return new RedirectResponse($this->cacheManager->resolve($path, $filter), 301);
        }

        $image = $this->dataManager->find($filter, $path);

        $response = $this->filterManager->get($request, $filter, $image, $path);

        return $this->cacheManager->store($response, $path, $filter);
    }
}
