<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Binary\BinaryInterface;

/**
 * ProxyResolver
 *
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class ProxyResolver implements ResolverInterface
{
    /**
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * a list of proxy hosts (picks a random one for each generation to seed browser requests among multiple hosts)
     *
     * @var array
     */
    protected $hosts = array();

    /**
     * @param ResolverInterface $resolver
     * @param string[]          $hosts
     */
    public function __construct(ResolverInterface $resolver, array $hosts)
    {
        $this->resolver = $resolver;
        $this->hosts = $hosts;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($path, $filter, array $runtimeConfig = array())
    {
        return $this->rewriteUrl($this->resolver->resolve($path, $filter, $runtimeConfig));
    }

    /**
     * {@inheritDoc}
     */
    public function store(BinaryInterface $binary, $targetPath, $filter, array $runtimeConfig = array())
    {
        return $this->resolver->store($binary, $targetPath, $filter, $runtimeConfig);
    }

    /**
     * {@inheritDoc}
     */
    public function isStored($path, $filter, array $runtimeConfig = array())
    {
        return $this->resolver->isStored($path, $filter, $runtimeConfig);
    }

    /**
     * {@inheritDoc}
     */
    public function remove(array $paths, array $filters, array $runtimeConfig = array())
    {
        return $this->resolver->remove($paths, $filters, $runtimeConfig);
    }

    /**
     * @param $url
     *
     * @return string
     */
    protected function rewriteUrl($url)
    {
        if (empty($this->hosts)) {
            return $url;
        }

        $host = parse_url($url, PHP_URL_SCHEME).'://'.parse_url($url, PHP_URL_HOST);
        $proxyHost = $this->hosts[rand(0, count($this->hosts) - 1)];

        return str_replace($host, $proxyHost, $url);
    }
}
