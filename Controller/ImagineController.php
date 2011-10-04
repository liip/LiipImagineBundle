<?php

namespace Liip\ImagineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
    public function __construct(LoaderInterface $dataLoader, FilterManager $filterManager, CachePathResolver $cachePathResolver = null)
    {
        $this->dataLoader        = $dataLoader;
        $this->filterManager     = $filterManager;
        $this->cachePathResolver = $cachePathResolver;
    }

    /**
     * Resolve the requested path to a local path or a Response
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param string $path
     * @param string $filter
     *
     * @return string|Symfony\Component\HttpFoundation\Response
     */
    protected function resolve(Request $request, $path, $filter)
    {
        $realPath = null;
        if ($this->cachePathResolver) {
            $realPath = $this->cachePathResolver->resolve($request, $path, $filter);
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
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param string $path
     * @param string $filter
     *
     * @return Response
     */
    public function filterAction(Request $request, $path, $filter)
    {
        list($actualPath, $image, $format) = $this->dataLoader->find($path);

        $realPath = $this->resolve($request, $actualPath, $filter);
        if ($realPath instanceof Response) {
            return $realPath;
        }

        $image = $this->filterManager->get($filter, $image, $realPath, $format);
        $statusCode = $this->cachePathResolver ? 201 : 200;
        $contentType = 'image/'.($format == 'jpg' ? 'jpeg' : $format);
        return new Response($image, $statusCode, array('Content-Type' => $contentType));
    }
}
