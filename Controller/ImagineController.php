<?php

namespace Avalanche\Bundle\ImagineBundle\Controller;

use Avalanche\Bundle\ImagineBundle\Imagine\CachePathResolver;
use Avalanche\Bundle\ImagineBundle\Imagine\Filter\FilterManager;
use Imagine\ImagineInterface;
use Symfony\Component\HttpKernel\Util\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImagineController
{
    /**
     * @var Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var Avalanche\Bundle\ImagineBundle\Imagine\CachePathResolver
     */
    private $cachePathResolver;

    /**
     * @var Imagine\ImagineInterface
     */
    private $imagine;

    /**
     * @var Avalanche\Bundle\ImagineBundle\Imagine\FilterManager
     */
    private $filterManager;

    /**
     * @var Symfony\Component\HttpKernel\Util\Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $webRoot;

    /**
     * Constructs by setting $cachePathResolver
     *
     * @param Symfony\Component\HttpFoundation\Request                 $request
     * @param Avalanche\Bundle\ImagineBundle\Imagine\CachePathResolver $cachePathResolver
     * @param Imagine\ImagineInterface                                 $imagine
     * @param Avalanche\Bundle\ImagineBundle\Imagine\FilterManager     $filterManager
     * @param string                                                   $webRoot
     */
    public function __construct(
        Request $request,
        CachePathResolver $cachePathResolver,
        ImagineInterface $imagine,
        FilterManager $filterManager,
        Filesystem $filesystem,
        $webRoot
    )
    {
        $this->request           = $request;
        $this->cachePathResolver = $cachePathResolver;
        $this->imagine           = $imagine;
        $this->filterManager     = $filterManager;
        $this->filesystem        = $filesystem;
        $this->webRoot           = $webRoot;
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
    public function filter($path, $filter)
    {
        $path = '/'.ltrim($path, '/');

        //TODO: find out why I need double urldecode to get a valid path
        $browserPath = urldecode(urldecode($this->cachePathResolver->getBrowserPath($path, $filter)));
        $basePath = $this->request->getBaseUrl();

        if (!empty($this->baseUrl) && 0 === strpos($browserPath, $basePath)) {
             $browserPath = substr($browserPath, strlen($basePath));
        }

         // if cache path cannot be determined, return 404
        if (null === $browserPath) {
            throw new NotFoundHttpException('Image doesn\'t exist');
        }

        $realPath = $this->webRoot.$browserPath;
        $sourcePath = $this->webRoot.$path;

        // if the file has already been cached, we're probably not rewriting
        // correctly, hence make a 301 to proper location, so browser remembers
        if (file_exists($realPath)) {
            return new Response('', 301, array(
                'location' => $this->request->getBasePath().$browserPath
            ));
        }

        if (!file_exists($sourcePath)) {
            throw new NotFoundHttpException(sprintf(
                'Source image not found in "%s"', $sourcePath
            ));
        }

        $dir = pathinfo($realPath, PATHINFO_DIRNAME);

        if (!is_dir($dir)) {
            if (!$this->filesystem->mkdir($dir)) {
                throw new \RuntimeException(sprintf(
                    'Could not create directory %s', $dir
                ));
            }
        }

        ob_start();
        try {
            // TODO: get rid of hard-coded quality and format
            $this->filterManager->get($filter)
                ->apply($this->imagine->open($sourcePath))
                ->save($realPath, array('quality' => 100))
                ->show('png');

            // TODO: add more media headers
            return new Response(ob_get_clean(), 201, array(
                'content-type' => 'image/png',
            ));
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }
    }
}
