<?php

namespace Liip\ImagineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Liip\ImagineBundle\Imagine\CachePathResolver;
use Liip\ImagineBundle\Imagine\DataLoader\LoaderInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

class ImagineController
{
    /**
     * @var Liip\ImagineBundle\Imagine\DataLoader\LoaderInterface
     */
    private $dataLoader;

    /**
     * @var Liip\ImagineBundle\Imagine\Filter\FilterManager
     */
    private $filterManager;

    /**
     * @var string
     */
    private $webRoot;

    /**
     * @var Liip\ImagineBundle\Imagine\CachePathResolver
     */
    private $cachePathResolver;

    /**
     * Constructor
     *
     * @param Liip\ImagineBundle\Imagine\DataLoader\LoaderInterface $dataLoader
     * @param Liip\ImagineBundle\Imagine\Filter\FilterManager       $filterManager
     * @param Liip\ImagineBundle\Imagine\CachePathResolver          $cachePathResolver
     */
    public function __construct(LoaderInterface $dataLoader, FilterManager $filterManager, $webRoot, CachePathResolver $cachePathResolver = null)
    {
        $this->dataLoader = $dataLoader;
        $this->filterManager = $filterManager;
        $this->webRoot = realpath($webRoot);
        $this->cachePathResolver = $cachePathResolver;
    }

    /**
     * This action applies a given filter to a given image,
     * optionally saves the image and
     * outputs it to the browser at the same time
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param string $path
     * @param string $filter
     *
     * @return Response
     */
    public function filterAction(Request $request, $path, $filter)
    {
        $path = $this->webRoot.'/'.ltrim($path, '/');

        $targetPath = false;
        if ($this->cachePathResolver) {
            $targetPath = $this->cachePathResolver->resolve($request, $path, $filter);
            if ($targetPath instanceof Response) {
                return $targetPath;
            }
        }

        $image = $this->dataLoader->find($path);
        $response = $this->filterManager->get($request, $filter, $image, $path);

        if ($targetPath) {
            $response = $this->cachePathResolver->store($response, $targetPath);
        }

        return $response;
    }
}
