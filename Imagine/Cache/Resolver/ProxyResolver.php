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
    /**
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * a list of proxy hosts (picks a random one for each generation to seed browser requests among multiple hosts).
     *
     * @var array
     */
    protected $hosts = [];

    /**
     * @param string[] $hosts
     */
    public function __construct(ResolverInterface $resolver, array $hosts)
    {
        $this->resolver = $resolver;
        $this->hosts = $hosts;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($path, $filter)
    {
        return $this->rewriteUrl($this->resolver->resolve($path, $filter));
    }

    /**
     * {@inheritdoc}
     */
    public function store(BinaryInterface $binary, $targetPath, $filter)
    {
        return $this->resolver->store($binary, $targetPath, $filter);
    }

    /**
     * {@inheritdoc}
     */
    public function isStored($path, $filter)
    {
        return $this->resolver->isStored($path, $filter);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(array $paths, array $filters)
    {
        return $this->resolver->remove($paths, $filters);
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

        $randKey = array_rand($this->hosts, 1);

        // BC
        if (is_numeric($randKey)) {
            $port = parse_url($url, PHP_URL_PORT);
            $host = parse_url($url, PHP_URL_SCHEME).'://'.parse_url($url, PHP_URL_HOST).($port ? ':'.$port : '');
            $proxyHost = $this->hosts[$randKey];

            return str_replace($host, $proxyHost, $url);
        }

        if (0 === mb_strpos($randKey, 'regexp/')) {
            $regExp = mb_substr($randKey, 6);

            return preg_replace($regExp, $this->hosts[$randKey], $url);
        }

        return str_replace($randKey, $this->hosts[$randKey], $url);
    }
}
