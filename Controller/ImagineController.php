<?php

namespace Liip\ImagineBundle\Controller;

use Imagine\Exception\RuntimeException;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * @var UriSigner
     */
    protected $uriSigner;

    /**
     * @param DataManager   $dataManager
     * @param FilterManager $filterManager
     * @param CacheManager  $cacheManager
     * @param UriSigner     $uriSigner
     */
    public function __construct(
        DataManager $dataManager,
        FilterManager $filterManager,
        CacheManager $cacheManager,
        UriSigner $uriSigner
    ) {
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
        $this->cacheManager = $cacheManager;
        $this->uriSigner = $uriSigner;
    }

    /**
     * This action applies a given filter to a given image, optionally saves the image and outputs it to the browser at the same time.
     *
     * @param Request $request
     * @param string $path
     * @param string $filter
     *
     * @throws \RuntimeException
     * @throws BadRequestHttpException
     *
     * @return RedirectResponse
     */
    public function filterAction(Request $request, $path, $filter)
    {
        try {
            $runtimeConfig = array();
            $pathPrefix = '';
            if ($runtimeFilters = $request->query->get('filters', array())) {
                if (false == $this->uriSigner->check($request->getSchemeAndHttpHost().$request->getRequestUri())) {
                    throw new BadRequestHttpException('Signed url does not pass the sign check. Maybe it was modified by someone.');
                }

                $runtimeConfig['filters'] = $runtimeFilters;
                $pathPrefix = substr($request->query->get('_hash'), 0, 8).'/';
            }

            if (!$this->cacheManager->isStored($path, $filter)) {
                $binary = $this->dataManager->find($filter, $path);

                $this->cacheManager->store(
                    $this->filterManager->applyFilter($binary, $filter, $runtimeConfig),
                    $pathPrefix.$path,
                    $filter
                );
            }

            return new RedirectResponse($this->cacheManager->resolve($pathPrefix.$path, $filter), 301);
        } catch (RuntimeException $e) {
            throw new \RuntimeException(sprintf('Unable to create image for path "%s" and filter "%s". Message was "%s"', $path, $filter, $e->getMessage()), 0, $e);
        }
    }
}
