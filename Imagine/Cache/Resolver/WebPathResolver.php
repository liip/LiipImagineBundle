<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\CacheManagerAwareInterface,
    Liip\ImagineBundle\Imagine\Cache\CacheManager;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\HttpKernel\Util\Filesystem,
    Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WebPathResolver implements ResolverInterface, CacheManagerAwareInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var CacheManager;
     */
    private $cacheManager;

    /**
     * Constructs cache web path resolver
     *
     * @param Filesystem  $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem   = $filesystem;
    }

    /**
     * @param CacheManager $cacheManager
     */
    public function setCacheManager(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * Resolves filtered path for rendering in the browser
     *
     * @param Request $request
     * @param string $path
     * @param string $filter
     *
     * @return string target path
     */
    public function resolve(Request $request, $targetPath, $filter)
    {
        //TODO: find out why I need double urldecode to get a valid path
        $browserPath = urldecode(urldecode($this->cacheManager->getBrowserPath($targetPath, $filter)));

         // if cache path cannot be determined, return 404
        if (null === $browserPath) {
            throw new NotFoundHttpException('Image doesn\'t exist');
        }

        $basePath = $request->getBaseUrl();
         if (!empty($basePath) && 0 === strpos($browserPath, $basePath)) {
             $browserPath = substr($browserPath, strlen($basePath));
        }

        $targetPath = $this->cacheManager->getWebRoot().$browserPath;

        // if the file has already been cached, we're probably not rewriting
        // correctly, hence make a 301 to proper location, so browser remembers
        if (file_exists($targetPath)) {
            return new RedirectResponse($request->getBasePath().$browserPath);
        }

        return $targetPath;
    }

    /**
     * @throws \RuntimeException
     * @param Response $response
     * @param string $targetPath
     * @param string $filter
     *
     * @return Response
     */
    public function store(Response $response, $targetPath, $filter)
    {
        $dir = pathinfo($targetPath, PATHINFO_DIRNAME);

        if (!is_dir($dir) && !$this->filesystem->mkdir($dir)) {
            throw new \RuntimeException(sprintf(
                'Could not create directory %s', $dir
            ));
        }

        file_put_contents($targetPath, $response->getContent());

        $response->setStatusCode(201);

        return $response;
    }
}
