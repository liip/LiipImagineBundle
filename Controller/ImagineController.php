<?php

namespace Liip\ImagineBundle\Controller;

use Imagine\Image\ImagineInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\UriSigner;

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
     * @return RedirectResponse
     */
    public function filterAction($path, $filter, Request $request)
    {
        $runtimeConfig = array();
        $filterPostfix = '';
        if ($runtimeFilters = $request->query->get('filters', array())) {
            $signer = new UriSigner('aSecret');
            if (false == $signer->check($request->getRequestUri())) {
//                throw new BadRequestHttpException('');
            }

            $runtimeConfig['filters'] = $runtimeFilters;
            $filterPostfix = '+'.substr($request->query->get('_hash'), 0, 8);
        }

        if (!$this->cacheManager->isStored($path, $filter, $filterPostfix)) {
            $binary = $this->dataManager->find($filter, $path);

            $this->cacheManager->store(
                $this->filterManager->applyFilter($binary, $filter, $runtimeConfig),
                $path,
                $filter
            );
        }

        return new RedirectResponse($this->cacheManager->resolve($path, $filter, $filterPostfix), 301);
    }
}
