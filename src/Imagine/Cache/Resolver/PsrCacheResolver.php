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
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use function str_replace;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PsrCacheResolver implements ResolverInterface
{
    private const RESERVED_CHARACTERS = [
        '{',
        '}',
        '(',
        ')',
        '/',
        '\\',
        '@',
        ':',
        '.',
    ];

    private CacheItemPoolInterface $cache;

    private array $options = [];

    private ResolverInterface $resolver;

    /**
     * Constructor.
     *
     * Available options:
     * * global_prefix
     *   A prefix for all keys within the cache. This is useful to avoid colliding keys when using the same cache for different systems.
     * * prefix
     *   A "local" prefix for this wrapper. This is useful when re-using the same resolver for multiple filters.
     * * index_key
     *   The name of the index key being used to save a list of created cache keys regarding one image and filter pairing.
     */
    public function __construct(CacheItemPoolInterface $cache, ResolverInterface $cacheResolver, array $options = [], OptionsResolver $optionsResolver = null)
    {
        $this->cache = $cache;
        $this->resolver = $cacheResolver;

        if (null === $optionsResolver) {
            $optionsResolver = new OptionsResolver();
        }

        $this->configureOptions($optionsResolver);
        $this->options = $optionsResolver->resolve($options);
    }

    public function isStored(string $path, string $filter): bool
    {
        $cacheKey = $this->generateCacheKey($path, $filter);

        return
            $this->cache->hasItem($cacheKey) ||
            $this->resolver->isStored($path, $filter);
    }

    public function resolve(string $path, string $filter): string
    {
        $key = $this->generateCacheKey($path, $filter);
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return $item->get();
        }

        $resolved = $this->resolver->resolve($path, $filter);

        $item->set($resolved);
        $this->saveToCache($item);

        return $resolved;
    }

    public function store(BinaryInterface $binary, string $path, string $filter): void
    {
        $this->resolver->store($binary, $path, $filter);
    }

    public function remove(array $paths, array $filters): void
    {
        $this->resolver->remove($paths, $filters);

        foreach ($filters as $filter) {
            if (empty($paths)) {
                $this->removeFilter($filter);
            } else {
                foreach ($paths as $path) {
                    $this->removePathAndFilter($path, $filter);
                }
            }
        }
    }

    /**
     * Generate a unique cache key based on the given parameters.
     *
     * When overriding this method, ensure generateIndexKey is adjusted accordingly.
     *
     * @param string $path   The image path in use
     * @param string $filter The filter in use
     */
    private function generateCacheKey(string $path, string $filter): string
    {
        return implode('.', [
            $this->sanitizeCacheKeyPart($this->options['global_prefix']),
            $this->sanitizeCacheKeyPart($this->options['prefix']),
            $this->sanitizeCacheKeyPart($filter),
            $this->sanitizeCacheKeyPart($path),
        ]);
    }

    private function generateCacheKeyForFilter(string $filter): string
    {
        return $this->generateCacheKey('', $filter);
    }

    private function removePathAndFilter(string $path, string $filter): void
    {
        $indexKey = $this->generateIndexKey($this->generateCacheKey($path, $filter));
        $indexItem = $this->cache->getItem($indexKey);
        if (!$indexItem->isHit()) {
            return;
        }

        $index = $indexItem->get();

        $cacheKey = $this->generateCacheKey($path, $filter);

        if (false !== $indexIndex = array_search($cacheKey, $index, true)) {
            unset($index[$indexIndex]);
            $this->cache->deleteItem($cacheKey);
        }

        if (empty($index)) {
            $this->cache->deleteItem($indexKey);
        } else {
            $indexItem->set($index);
            $this->cache->save($indexItem);
        }
    }

    private function removeFilter(string $filter): void
    {
        $indexKey = $this->generateIndexKey($this->generateCacheKeyForFilter($filter));
        $indexItem = $this->cache->getItem($indexKey);

        if (!$indexItem->isHit()) {
            return;
        }

        $index = $indexItem->get();

        foreach ($index as $eachCacheKey) {
            $this->cache->deleteItem($eachCacheKey);
        }

        $this->cache->deleteItem($indexKey);
    }

    /**
     * Generate the index key for the given cacheKey.
     *
     * The index contains a list of cache keys related to an image and a filter.
     */
    private function generateIndexKey(string $cacheKey): string
    {
        $cacheKeyStack = explode('.', $cacheKey);

        return implode('.', [
            $this->sanitizeCacheKeyPart($this->options['global_prefix']),
            $this->sanitizeCacheKeyPart($this->options['prefix']),
            $this->sanitizeCacheKeyPart($this->options['index_key']),
            $this->sanitizeCacheKeyPart($cacheKeyStack[2]), // filter
        ]);
    }

    private function sanitizeCacheKeyPart(string $cacheKeyPart): string
    {
        return str_replace(self::RESERVED_CHARACTERS, '_', $cacheKeyPart);
    }

    /**
     * Save the given content to the cache and update the cache index.
     */
    private function saveToCache(CacheItemInterface $item): bool
    {
        $cacheKey = $item->getKey();

        // Create or update the index list containing all cache keys for a given image and filter pairing.
        $indexKey = $this->generateIndexKey($cacheKey);
        $indexItem = $this->cache->getItem($indexKey);
        if ($indexItem->isHit()) {
            $index = (array) $indexItem->get();

            if (!\in_array($cacheKey, $index, true)) {
                $index[] = $cacheKey;
            }
        } else {
            $index = [$cacheKey];
        }

        $indexItem->set($index);
        $this->cache->saveDeferred($indexItem);
        $this->cache->saveDeferred($item);

        return $this->cache->commit();
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'global_prefix' => 'liip_imagine.resolver_psr_cache',
            'prefix' => \get_class($this->resolver),
            'index_key' => 'index',
        ]);

        $allowedTypesList = [
          'global_prefix' => 'string',
          'prefix' => 'string',
          'index_key' => 'string',
        ];

        foreach ($allowedTypesList as $option => $allowedTypes) {
            $resolver->setAllowedTypes($option, $allowedTypes);
        }
    }
}
