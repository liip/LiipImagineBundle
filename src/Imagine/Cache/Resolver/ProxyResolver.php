<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Binary\BinaryInterface;

/**
 * ProxyResolver.
 *
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class ProxyResolver implements ResolverInterface
{
    protected ResolverInterface $resolver;

    /**
     * a list of proxy hosts (picks a random one for each generation to seed browser requests among multiple hosts).
     */
    protected array $hosts = [];

    /**
     * @param string[] $hosts
     */
    public function __construct(ResolverInterface $resolver, array $hosts)
    {
        $this->resolver = $resolver;
        $this->hosts = $hosts;
    }

    public function resolve(string $path, string $filter): string
    {
        return $this->rewriteUrl($this->resolver->resolve($path, $filter));
    }

    public function store(BinaryInterface $binary, string $path, string $filter): void
    {
        $this->resolver->store($binary, $path, $filter);
    }

    public function isStored(string $path, string $filter): bool
    {
        return $this->resolver->isStored($path, $filter);
    }

    public function remove(array $paths, array $filters): void
    {
        $this->resolver->remove($paths, $filters);
    }

    /**
     * If you need more complex logic, extend the proxy resolver and overwrite this method.
     */
    protected function rewriteUrl(string $url): string
    {
        if (empty($this->hosts)) {
            return $url;
        }

        $randKey = array_rand($this->hosts);

        $port = parse_url($url, PHP_URL_PORT);
        $host = parse_url($url, PHP_URL_SCHEME).'://'.parse_url($url, PHP_URL_HOST).($port ? ':'.$port : '');
        $proxyHost = $this->hosts[$randKey];

        return str_replace($host, $proxyHost, $url);
    }
}
