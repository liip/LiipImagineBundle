<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Symfony\Component\Finder\Finder;

class WebPathResolver extends AbstractFilesystemResolver
{
    /**
     * If the file has already been cached, we're probably not rewriting
     * correctly, hence make a 301 to proper location, so browser remembers.
     *
     * Strip the base URL of this request from the browserpath to not interfere with the base path.
     *
     * {@inheritDoc}
     */
    public function resolve($path, $filter)
    {
        $browserPath = $this->decodeBrowserPath($this->getBrowserPath($path, $filter));
        $this->basePath = $this->getRequest()->getBaseUrl();

        if ($this->basePath && 0 === strpos($browserPath, $this->basePath)) {
            $browserPath = substr($browserPath, strlen($this->basePath));
        }

        return $this->getRequest()->getBasePath().$browserPath;
    }

    /**
     * {@inheritDoc}
     */
    public function getBrowserPath($path, $filter, $absolute = false)
    {
        return $this->cacheManager->generateUrl($path, $filter, $absolute);
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

        $cachePath = $this->cacheManager->getWebRoot() . $cachePrefix;

        // Avoid an exception if the cache path does not exist (i.e. Imagine didn't yet render any image)
        if (is_dir($cachePath)) {
            $this->filesystem->remove(Finder::create()->in($cachePath)->depth(0)->directories());
        }
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
