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

    public function __construct(CacheManager $cache, ?string $assetVersion = null, ?array $jsonManifest = null)
    {
        $this->cache = $cache;
        $this->assetVersion = $assetVersion;
        $this->jsonManifest = $jsonManifest;
        $this->jsonManifestLookup = $jsonManifest ? array_flip($jsonManifest) : null;
    }

    /**
     * Gets the browser path for the image and filter to apply.
     */
    public function filter(string $path, string $filter, array $config = [], ?string $resolver = null, int $referenceType = UrlGeneratorInterface::ABSOLUTE_URL): string
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
    public function filterCache(string $path, string $filter, array $config = [], ?string $resolver = null): string
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
        // strip absolute URLs (e.g. remote filesystems like S3) to their path
        // host guard: parse_url(PHP_URL_PATH) would also truncate local names at "?"/"#" and strip the version query string
        if (null !== parse_url($path, PHP_URL_HOST)) {
            $path = parse_url($path, PHP_URL_PATH) ?? $path;
        }

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
            $prefixedSlash = 0 !== mb_strpos($path, '/') && 0 === mb_strpos($this->jsonManifest[$path], '/');
            $versionedPath = $prefixedSlash ? mb_substr($this->jsonManifest[$path], 1) : $this->jsonManifest[$path];

            $originalExt = pathinfo($path, PATHINFO_EXTENSION);
            $resolvedExt = pathinfo($resolvedPath, PATHINFO_EXTENSION);

            if ($originalExt !== $resolvedExt) {
                $path = str_replace('.'.$originalExt, '.'.$resolvedExt, $path);
                $versionedPath = str_replace('.'.$originalExt, '.'.$resolvedExt, $versionedPath);
            }

            $versioning = $this->captureVersion(pathinfo($path, PATHINFO_BASENAME), pathinfo($versionedPath, PATHINFO_BASENAME));
            $resolvedFilename = pathinfo($resolvedPath, PATHINFO_BASENAME);
            $resolvedDir = pathinfo($resolvedPath, PATHINFO_DIRNAME);
            $resolvedPath = $resolvedDir.'/'.$this->insertVersion($resolvedFilename, $versioning['version'], $versioning['position']);
        }

        return $resolvedPath;
    }

    /**
     * Capture the versioning string from the versioned filename
     */
    private function captureVersion(string $originalFilename, string $versionedFilename): array
    {
        $originalLength = mb_strlen($originalFilename);
        $versionedLength = mb_strlen($versionedFilename);

        for ($i = 0; $i < $originalLength && $i < $versionedLength; ++$i) {
            if ($originalFilename[$i] !== $versionedFilename[$i]) {
                break;
            }
        }

        $version = mb_substr($versionedFilename, $i, $versionedLength - $originalLength);

        return ['version' => $version, 'position' => $i];
    }

    /**
     * Insert the version string into our resolved filename
     */
    private function insertVersion(string $resolvedFilename, string $version, int $position): string
    {
        if ($position < 0 || $position > mb_strlen($resolvedFilename)) {
            return $resolvedFilename;
        }

        $firstPart = mb_substr($resolvedFilename, 0, $position);
        $secondPart = mb_substr($resolvedFilename, $position);

        return $firstPart.$version.$secondPart;
    }
}
