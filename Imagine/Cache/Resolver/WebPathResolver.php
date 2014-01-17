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

    /**
     * {@inheritDoc}
     */
    protected function getFilePath($path, $filter)
    {
        return $this->cacheManager->getWebRoot().$this->resolve($path, $filter);
    }
}
