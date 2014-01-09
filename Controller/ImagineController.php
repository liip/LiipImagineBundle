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
     * @var ImagineInterface
     */
    protected $imagine;

    /**
     * @param DataManager $dataManager
     * @param FilterManager $filterManager
     * @param CacheManager $cacheManager
     * @param ImagineInterface $imagine
     */
    public function __construct(DataManager $dataManager, FilterManager $filterManager, CacheManager $cacheManager, ImagineInterface $imagine)
    {
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
        $this->cacheManager = $cacheManager;
        $this->imagine = $imagine;
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

        $binary = $this->dataManager->find($filter, $path);
        $image = $this->imagine->load($binary->getContent());

        $filteredImage = $this->filterManager->applyFilter($image, $filter, array(
            'format' => $binary->getFormat()
        ));
        $response = $this->filterManager->get($request, $filter, $filteredImage, $path);

        return $this->cacheManager->store($response, $path, $filter);
    }
}
