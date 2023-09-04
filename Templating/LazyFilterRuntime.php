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

    /**
     * @var array|null
     */
    private $jsonManifest;

    /**
     * @var array|null
     */
    private $jsonManifestLookup;

    public function __construct(CacheManager $cache, string $assetVersion = null, array $jsonManifest = null)
    {
        $this->cache = $cache;
        $this->assetVersion = $assetVersion;
        $this->jsonManifest = $jsonManifest;
        $this->jsonManifestLookup = $jsonManifest ? array_flip($jsonManifest) : null;
    }

    /**
     * Gets the browser path for the image and filter to apply.
     */
    public function filter(string $path, string $filter, array $config = [], string $resolver = null, int $referenceType = UrlGeneratorInterface::ABSOLUTE_URL): string
    {
        $path = $this->cleanPath($path);
        $resolvedPath = $this->cache->getBrowserPath($path, $filter, $config, $resolver, $referenceType);

        return $this->appendAssetVersion($resolvedPath, $path);
    }

    /**
     * Gets the cache path for the image and filter to apply.
     *
     * This does not check whether the cached image exists or not.
     */
    public function filterCache(string $path, string $filter, array $config = [], string $resolver = null): string
    {
        $path = $this->cleanPath($path);
        if (\count($config)) {
            $path = $this->cache->getRuntimePath($path, $config);
        }
        $resolvedPath = $this->cache->resolve($path, $filter, $resolver);

        return $this->appendAssetVersion($resolvedPath, $path);
    }

    private function cleanPath(string $path): string
    {
        if (!$this->assetVersion && !$this->jsonManifest) {
            return $path;
        }

        if ($this->assetVersion) {
            $start = mb_strrpos($path, $this->assetVersion);
            if (mb_strlen($path) - mb_strlen($this->assetVersion) === $start) {
                return rtrim(mb_substr($path, 0, $start), '?');
            }
        }

        if ($this->jsonManifest) {
            if (\array_key_exists($path, $this->jsonManifestLookup)) {
                return $this->jsonManifestLookup[$path];
            }
        }

        return $path;
    }

    private function appendAssetVersion(string $resolvedPath, string $path): string
    {
        if (!$this->assetVersion && !$this->jsonManifest) {
            return $resolvedPath;
        }

        if ($this->assetVersion) {
            $separator = false !== mb_strpos($resolvedPath, '?') ? '&' : '?';

            return $resolvedPath.$separator.$this->assetVersion;
        }

        if (\array_key_exists($path, $this->jsonManifest)) {
            $resolvedPath = str_replace($path, $this->jsonManifest[$path], $resolvedPath);
        }

        return $resolvedPath;
    }
}
