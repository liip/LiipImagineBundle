<?php

namespace Liip\ImagineBundle\Controller;

use Imagine\Image\ImagineInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

use Liip\ImagineBundle\Util\SignerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     * @var SignerInterface
     */
    protected $signer;

    /**
     * @param DataManager     $dataManager
     * @param FilterManager   $filterManager
     * @param CacheManager    $cacheManager
     * @param SignerInterface $signer
     */
    public function __construct(
        DataManager $dataManager,
        FilterManager $filterManager,
        CacheManager $cacheManager,
        SignerInterface $signer
    ) {
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
        $this->cacheManager = $cacheManager;
        $this->signer = $signer;
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
        /**
         * Query params should always stay on the image - Could be used for tracking/caching
         * It is especially important for runtime config images
         * If the cache is deleted and you refresh a runtime config request it
         * means that the image can be re-resolved used the query params hash etc
         */
        $queryString = strlen($request->server->get('QUERY_STRING')) > 0 ? '?'.$request->server->get('QUERY_STRING') : '';

        try {
            $runtimeConfig = array();
            $pathPrefix = '';
            if ($runtimeFilters = $request->query->get('filters', array())) {
                // Runtime config images have the trimmed hash prepended
                list($requestedPrefix, $path) = explode("/", $path, 2);

                if (false == $this->signer->checkHash($path, $runtimeFilters, $request->query->get('_hash'))) {
                    throw new BadRequestHttpException('Signed url does not pass the sign check. Maybe it was modified by someone.');
                }

                $pathPrefix = $this->signer->getHash($path, $runtimeFilters, true).'/';
                if ($pathPrefix !== $requestedPrefix.'/') {
                    throw new BadRequestHttpException('Path prefix does not match.');
                }

                $runtimeConfig['filters'] = $runtimeFilters;
            }

            if (!$this->cacheManager->isStored($path, $filter)) {
                $binary = $this->dataManager->find($filter, $path);

                $this->cacheManager->store(
                    $this->filterManager->applyFilter($binary, $filter, $runtimeConfig),
                    $pathPrefix.$path,
                    $filter
                );
            }

            return new RedirectResponse($this->cacheManager->resolve($pathPrefix.$path, $filter).$queryString, 301);
        } catch (RuntimeException $e) {
            throw new \RuntimeException(sprintf('Unable to create image for path "%s" and filter "%s". Message was "%s"', $pathPrefix.$path, $filter, $e->getMessage()), 0, $e);
        }

        return new RedirectResponse($this->cacheManager->resolve($path, $filter).$queryString, 301);
    }
}
