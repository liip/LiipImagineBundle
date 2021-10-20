<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Templating;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class LazyFilterRuntime implements RuntimeExtensionInterface
{
    /**
     * @var CacheManager
     */
    private $cache;

    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Gets the browser path for the image and filter to apply.
     */
    public function filter(string $path, string $filter, array $config = [], ?string $resolver = null, int $referenceType = UrlGeneratorInterface::ABSOLUTE_URL): string
    {
        return $this->cache->getBrowserPath($this->cleanPath($path), $filter, $config, $resolver, $referenceType);
    }

    /**
     * Gets the cache path for the image and filter to apply.
     *
     * This does not check whether the cached image exists or not.
     */
    public function filterCache(string $path, string $filter, array $config = [], ?string $resolver = null): string
    {
        $path = $this->cleanPath($path);

        if (!empty($config)) {
            $path = $this->cache->getRuntimePath($path, $config);
        }

        return $this->cache->resolve($path, $filter, $resolver);
    }

    private function cleanPath(string $path): string
    {
        $url = parse_url($path);
        $path = $url['path'] ?? '';
        if (array_key_exists('query', $url)) {
            $path .= '?'.$url['query'];
        }
        if (array_key_exists('fragment', $url)) {
            $path .= '#'.$url['fragment'];
        }

        return $path;
    }
}
