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

    /**
     * Optional version to remove from the asset filename and re-append to the URL.
     *
     * @var string|null
     */
    private $assetVersion;

    public function __construct(CacheManager $cache, ?string $assetVersion = null)
    {
        $this->cache = $cache;
        $this->assetVersion = $assetVersion;
    }

    /**
     * Gets the browser path for the image and filter to apply.
     */
    public function filter(string $path, string $filter, array $config = [], ?string $resolver = null, int $referenceType = UrlGeneratorInterface::ABSOLUTE_URL): string
    {
        $path = $this->cleanPath($path);
        $path = $this->cache->getBrowserPath($path, $filter, $config, $resolver, $referenceType);

        return $this->appendAssetVersion($path);
    }

    /**
     * Gets the cache path for the image and filter to apply.
     *
     * This does not check whether the cached image exists or not.
     */
    public function filterCache(string $path, string $filter, array $config = [], ?string $resolver = null): string
    {
        $path = $this->cleanPath($path);
        if (\count($config)) {
            $path = $this->cache->getRuntimePath($path, $config);
        }
        $path = $this->cache->resolve($path, $filter, $resolver);

        return $this->appendAssetVersion($path);
    }

    private function cleanPath(string $path): string
    {
        if (!$this->assetVersion) {
            return $path;
        }

        $start = mb_strrpos($path, $this->assetVersion);
        if (mb_strlen($path) - mb_strlen($this->assetVersion) === $start) {
            return rtrim(mb_substr($path, 0, $start), '?');
        }

        return $path;
    }

    private function appendAssetVersion(string $path): string
    {
        if (!$this->assetVersion) {
            return $path;
        }

        $separator = false !== mb_strpos($path, '?') ? '&' : '?';

        return $path.$separator.$this->assetVersion;
    }
}
