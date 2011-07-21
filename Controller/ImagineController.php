<?php

namespace Avalanche\Bundle\ImagineBundle\Controller;

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
     * Constructs by setting $cachePathResolver
     *
     * @param Avalanche\Bundle\ImagineBundle\Imagine\DataLoader\LoaderInterface $dataLoader
     * @param Avalanche\Bundle\ImagineBundle\Imagine\Filter\FilterManager       $filterManager
     * @param Avalanche\Bundle\ImagineBundle\Imagine\CachePathResolver          $cachePathResolver
     */
    public function __construct(LoaderInterface $dataLoader, FilterManager $filterManager, CachePathResolver $cachePathResolver = null)
    {
        $this->dataLoader        = $dataLoader;
        $this->filterManager     = $filterManager;
        $this->cachePathResolver = $cachePathResolver;
    }

    /**
     * This action applies a given filter to a given image, saves the image and
     * outputs it to the browser at the same time
     *
     * @param string $path
     * @param string $filter
     *
     * @return Response
     */
    public function filterAction($path, $filter)
    {
        list($path, $image, $format) = $this->dataLoader->find($path);
        if (!$path) {
            throw new NotFoundHttpException(sprintf(
                'Source image not found in "%s"', $path
            ));
        }

        if ('json' === $format) {
            return new Response($image, 200, array('Content-Type' => 'application/json'));
        }

        $realPath = null;
        if ($this->cachePathResolver) {
            $realPath = $this->cachePathResolver->resolve($path, $filter);
            if (!$realPath) {
                throw new NotFoundHttpException('Image doesn\'t exist');
            }

            if ($realPath instanceof Response) {
                return $realPath;
            }
        }

        $image = $this->filterManager->get($filter, $image, $realPath, $format);
        $statusCode = $this->cachePathResolver ? 201 : 200;
        return new Response($image, $statusCode, array('Content-Type' => 'image/'.$format));
    }
}
