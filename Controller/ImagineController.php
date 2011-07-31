<?php

namespace Avalanche\Bundle\ImagineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Avalanche\Bundle\ImagineBundle\Imagine\CachePathResolver;
use Avalanche\Bundle\ImagineBundle\Imagine\DataLoader\LoaderInterface;
use Avalanche\Bundle\ImagineBundle\Imagine\Filter\FilterManager;

class ImagineController
{
    /**
     * @var Avalanche\Bundle\ImagineBundle\Imagine\DataLoader\LoaderInterface
     */
    private $dataLoader;

    /**
     * @var Avalanche\Bundle\ImagineBundle\Imagine\Filter\FilterManager
     */
    private $filterManager;

    /**
     * @var Avalanche\Bundle\ImagineBundle\Imagine\CachePathResolver
     */
    private $cachePathResolver;

    /**
     * @var Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * Constructor
     *
     * @param Avalanche\Bundle\ImagineBundle\Imagine\DataLoader\LoaderInterface $dataLoader
     * @param Avalanche\Bundle\ImagineBundle\Imagine\Filter\FilterManager       $filterManager
     * @param Avalanche\Bundle\ImagineBundle\Imagine\CachePathResolver          $cachePathResolver
     * @param Symfony\Component\HttpFoundation\Request                          $request
     */
    public function __construct(LoaderInterface $dataLoader, FilterManager $filterManager, CachePathResolver $cachePathResolver = null, Request $request = null)
    {
        $this->dataLoader        = $dataLoader;
        $this->filterManager     = $filterManager;
        $this->cachePathResolver = $cachePathResolver;
        $this->request           = $request;
    }

    protected function resolve($path, $filter)
    {
        $realPath = null;
        if ($this->cachePathResolver) {
            $realPath = $this->cachePathResolver->resolve($this->request, $path, $filter);
            if (!$realPath) {
                throw new NotFoundHttpException('Image doesn\'t exist');
            }
        }

        return $realPath;
    }

    /**
     * This action applies a given filter to a given image,
     * optionally saves the image and
     * outputs it to the browser at the same time
     *
     * @param string $path
     * @param string $filter
     *
     * @return Response
     */
    public function filterAction($path, $filter)
    {
        list($actualPath, $image, $format) = $this->dataLoader->find($path);

        $realPath = $this->resolve($actualPath, $filter);
        if ($realPath instanceof Response) {
            return $realPath;
        }

        $image = $this->filterManager->get($filter, $image, $realPath, $format);
        $statusCode = $this->cachePathResolver ? 201 : 200;
        $contentType = 'image/'.($format == 'jpg' ? 'jpeg' : $format);
        return new Response($image, $statusCode, array('Content-Type' => $contentType));
    }
}
