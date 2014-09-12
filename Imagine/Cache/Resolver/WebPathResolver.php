<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\SignerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RequestContext;

class WebPathResolver implements ResolverInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var RequestContext
     */
    protected $requestContext;

    /**
     * @var \Liip\ImagineBundle\Imagine\Cache\SignerInterface
     */
    protected $signer;

    /**
     * @var string
     */
    protected $webRoot;
    /**
     * @var string
     */
    protected $cachePrefix;

    /**
     * @param Filesystem $filesystem
     * @param RequestContext $requestContext
     * @param SignerInterface $signer
     * @param string $webRootDir
     * @param string $cachePrefix
     */
    public function __construct(
        Filesystem $filesystem,
        RequestContext $requestContext,
        SignerInterface $signer,
        $webRootDir,
        $cachePrefix = 'media/cache'
    ) {
        $this->filesystem = $filesystem;
        $this->requestContext = $requestContext;
        $this->signer = $signer;

        $this->webRoot = rtrim(str_replace('//', '/', $webRootDir), '/');
        $this->cachePrefix = ltrim(str_replace('//', '/', $cachePrefix), '/');
        $this->cacheRoot = $this->webRoot.'/'.$this->cachePrefix;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($path, $filter, array $runtimeConfig = array())
    {
        return sprintf('%s/%s',
            $this->getBaseUrl(),
            $this->getFileUrl($path, $filter, $runtimeConfig)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isStored($path, $filter, array $runtimeConfig = array())
    {
        return $this->filesystem->exists($this->getFilePath($path, $filter, $runtimeConfig));
    }

    /**
     * {@inheritDoc}
     */
    public function store(BinaryInterface $binary, $path, $filter, array $runtimeConfig = array())
    {
        $this->filesystem->dumpFile(
            $this->getFilePath($path, $filter, $runtimeConfig),
            $binary->getContent()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function remove(array $paths, array $filters, array $runtimeConfig = array())
    {
        if (empty($paths) && empty($filters)) {
            return;
        }

        if (empty($paths)) {
            $filtersCacheDir = array();
            foreach ($filters as $filter) {
                $filtersCacheDir[] = $this->cacheRoot.'/'.$filter;
            }

            $this->filesystem->remove($filtersCacheDir);

            return;
        }

        foreach ($paths as $path) {
            foreach ($filters as $filter) {
                $this->filesystem->remove($this->getFilePath($path, $filter, $runtimeConfig));
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getFilePath($path, $filter, array $runtimeConfig = array())
    {
        return $this->webRoot.'/'.$this->getFileUrl($path, $filter, $runtimeConfig);
    }

    /**
     * {@inheritDoc}
     */
    protected function getFileUrl($path, $filter, array $runtimeConfig = array())
    {
        if (empty($runtimeConfig)) {
            return $this->cachePrefix.'/'.$filter.'/'.ltrim($path, '/');
        } else {
            return $this->cachePrefix.'/'.$filter.'/rc/'.$this->signer->sign($path, $runtimeConfig).'/'.ltrim($path, '/');
        }
    }

    /**
     * @return string
     */
    protected function getBaseUrl()
    {
        $port = '';
        if ('https' == $this->requestContext->getScheme() && $this->requestContext->getHttpsPort() != 443) {
            $port =  ":{$this->requestContext->getHttpsPort()}";
        }

        if ('http' == $this->requestContext->getScheme() && $this->requestContext->getHttpPort() != 80) {
            $port =  ":{$this->requestContext->getHttpPort()}";
        }

        $baseUrl = $this->requestContext->getBaseUrl();
        if ('.php' == substr($this->requestContext->getBaseUrl(), -4)) {
            $baseUrl = pathinfo($this->requestContext->getBaseurl(), PATHINFO_DIRNAME);
        }
        $baseUrl = rtrim($baseUrl, '/\\');

        return sprintf('%s://%s%s%s',
            $this->requestContext->getScheme(),
            $this->requestContext->getHost(),
            $port,
            $baseUrl
        );
    }
}