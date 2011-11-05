<?php

namespace Liip\ImagineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Liip\ImagineBundle\Imagine\CachePathResolver;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

class ImagineController
{
    /**
     * @var DataManager
     */
    private $dataManager;

    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @var CachePathResolver
     */
    private $cachePathResolver;

    /**
     * Constructor
     *
     * @param DataManager $dataManager
     * @param FilterManager $filterManager
     * @param CachePathResolver $cachePathResolver
     */
    public function __construct(DataManager $dataManager, FilterManager $filterManager, CachePathResolver $cachePathResolver = null)
    {
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
        $this->cachePathResolver = $cachePathResolver;
    }

    /**
     * This action applies a given filter to a given image,
     * optionally saves the image and
     * outputs it to the browser at the same time
     *
     * @param Request $request
     * @param string $path
     * @param string $filter
     *
     * @return Response
     */
    public function filterAction(Request $request, $path, $filter)
    {
        $targetPath = false;
        if ($this->cachePathResolver) {
            $targetPath = $this->cachePathResolver->resolve($request, $path, $filter);
            if ($targetPath instanceof Response) {
                return $targetPath;
            }
        }

        $image = $this->dataManager->find($filter, $path);
        $response = $this->filterManager->get($request, $filter, $image, $path);

        if ($targetPath && $response->isSuccessful()) {
            $response = $this->cachePathResolver->store($response, $targetPath);
        }

        return $response;
    }
}
