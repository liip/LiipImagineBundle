<?php

namespace Liip\ImagineBundle\Controller;

use Imagine\Image\ImagineInterface;
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
     * @param string $path
     * @param string $filter
     *
     * @return Response
     */
    public function filterAction($path, $filter)
    {
        if ($this->cacheManager->isStored($path, $filter)) {
            return new RedirectResponse($this->cacheManager->resolve($path, $filter), 301);
        }

        $binary = $this->dataManager->find($filter, $path);

        $filteredBinary = $this->filterManager->applyFilter($binary, $filter);

        // Usage of response will be replaced by next PR.
        $response = new Response($filteredBinary->getContent(), 200, array(
            'Content-Type' => $filteredBinary->getMimeType(),
        ));

        return $this->cacheManager->store($response, $path, $filter);
    }
}
