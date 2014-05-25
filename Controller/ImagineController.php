<?php

namespace Liip\ImagineBundle\Controller;

use Imagine\Exception\RuntimeException;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Imagine\Cache\SignerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @var bool
     */
    protected $redirect;

    /**
     * @param DataManager     $dataManager
     * @param FilterManager   $filterManager
     * @param CacheManager    $cacheManager
     * @param SignerInterface $signer
     * @param bool            $redirect
     */
    public function __construct(
        DataManager $dataManager,
        FilterManager $filterManager,
        CacheManager $cacheManager,
        SignerInterface $signer,
        $redirect
    ) {
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
        $this->cacheManager = $cacheManager;
        $this->signer = $signer;
        $this->redirect = $redirect;
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
            if (!$this->cacheManager->isStored($path, $filter)) {
                try {
                    $binary = $this->dataManager->find($filter, $path);
                } catch (NotLoadableException $e) {

                    throw new NotFoundHttpException('Source image could not be found', $e);
                }

                $resultingImage = $this->filterManager->applyFilter($binary, $filter);
                $this->cacheManager->store(
                    $resultingImage,
                    $path,
                    $filter
                );
                if(false === $this->redirect) {

                return new Response($resultingImage, 200, array('Content-type'=>$binary->getMimeType()));
                }
            }

            return new RedirectResponse($this->cacheManager->resolve($path, $filter), 301);

        } catch (RuntimeException $e) {
            throw new \RuntimeException(sprintf('Unable to create image for path "%s" and filter "%s". Message was "%s"', $path, $filter, $e->getMessage()), 0, $e);
        }
    }

    /**
     * This action applies a given filter to a given image, optionally saves the image and outputs it to the browser at the same time.
     *
     * @param Request $request
     * @param string  $hash
     * @param string  $path
     * @param string  $filter
     *
     * @throws \RuntimeException
     * @throws BadRequestHttpException
     *
     * @return RedirectResponse
     */
    public function filterRuntimeAction(Request $request, $hash, $path, $filter)
    {
        try {
            $filters = $request->query->get('filters', array());

            if (true !== $this->signer->check($hash, $path, $filters)) {
                throw new BadRequestHttpException(sprintf(
                    'Signed url does not pass the sign check for path "%s" and filter "%s" and runtime config %s',
                    $path,
                    $filter,
                    json_encode($filters)
                ));
            }

            try {
                $binary = $this->dataManager->find($filter, $path);
            } catch (NotLoadableException $e) {
                throw new NotFoundHttpException(sprintf('Source image could not be found for path "%s" and filter "%s"', $path, $filter), $e);
            }

            $cachePrefix = 'rc/'.$hash;

            $this->cacheManager->store(
                $this->filterManager->applyFilter($binary, $filter, array(
                    'filters' => $filters,
                )),
                $cachePrefix.'/'.$path,
                $filter
            );

            return new RedirectResponse($this->cacheManager->resolve($cachePrefix.'/'.$path, $filter), 301);
        } catch (RuntimeException $e) {
            throw new \RuntimeException(sprintf('Unable to create image for path "%s" and filter "%s". Message was "%s"', $hash.'/'.$path, $filter, $e->getMessage()), 0, $e);
        }
    }
}
