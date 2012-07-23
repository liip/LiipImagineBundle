<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\CacheManagerAwareInterface,
    Liip\ImagineBundle\Imagine\Cache\CacheManager;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\HttpKernel\Exception\NotFoundHttpException,
    Symfony\Component\Finder\Finder;

class WebPathResolver extends AbstractFilesystemResolver implements CacheManagerAwareInterface
{
    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @param CacheManager $cacheManager
     */
    public function setCacheManager(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Request $request, $path, $filter)
    {
        $browserPath = $this->decodeBrowserPath($this->getBrowserPath($path, $filter));
        $this->basePath = $request->getBaseUrl();
        $targetPath = $this->getFilePath($path, $filter);

        // if the file has already been cached, we're probably not rewriting
        // correctly, hence make a 301 to proper location, so browser remembers
        if (file_exists($targetPath)) {
            $scriptName = $request->getScriptName();
            if (strpos($browserPath, $scriptName) === 0) {
                // strip script name
                $browserPath = substr($browserPath, strlen($scriptName));
            }

            return new RedirectResponse($request->getBasePath().$browserPath);
        }

        return $targetPath;
    }

    /**
     * {@inheritDoc}
     */
    public function getBrowserPath($targetPath, $filter, $absolute = false)
    {
        return $this->cacheManager->generateUrl($targetPath, $filter, $absolute);
    }

    /**
     * {@inheritDoc}
     */
    public function clear($cachePrefix)
    {
        // Let's just avoid to remove the web/ directory content if cache prefix is empty
        if ($cachePrefix === '') {
            throw new \InvalidArgumentException("Cannot clear the Imagine cache because the cache_prefix is empty in your config.");
        }

        $cachePath = $this->cacheManager->getWebRoot() . DIRECTORY_SEPARATOR . $cachePrefix;

        // Avoid an exception if the cache path does not exist (i.e. Imagine didn't yet render any image)
        if (is_dir($cachePath)) {
            $this->filesystem->remove(Finder::create()->in($cachePath)->depth(0)->directories());
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getFilePath($path, $filter)
    {
        $browserPath = $this->decodeBrowserPath($this->getBrowserPath($path, $filter));

        // if cache path cannot be determined, return 404
        if (null === $browserPath) {
            throw new NotFoundHttpException('Image doesn\'t exist');
        }

        if (!empty($this->basePath) && 0 === strpos($browserPath, $this->basePath)) {
            $browserPath = substr($browserPath, strlen($this->basePath));
        }

        return $this->cacheManager->getWebRoot().$browserPath;
    }

    /**
     * Decodes the URL encoded browser path.
     *
     * @param string $browserPath
     *
     * @return string
     */
    protected function decodeBrowserPath($browserPath)
    {
        //TODO: find out why I need double urldecode to get a valid path
        return urldecode(urldecode($browserPath));
    }
}
