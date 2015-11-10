<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Doctrine\Common\Cache\Cache;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\HttpKernel\Kernel;

class CacheResolver implements ResolverInterface
{
    /**
     * @var Cache
     */
    protected $cache;

    protected $options = array();

    /**
     * @var ResolverInterface
     */
    protected $resolver;

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
     *
     * @param Cache                    $cache
     * @param ResolverInterface        $cacheResolver
     * @param array                    $options
     * @param OptionsResolverInterface $optionsResolver
     */
    public function __construct(Cache $cache, ResolverInterface $cacheResolver, array $options = array(), OptionsResolverInterface $optionsResolver = null)
    {
        $this->cache = $cache;
        $this->resolver = $cacheResolver;

        if (null === $optionsResolver) {
            $optionsResolver = new OptionsResolver();
        }

        $this->configureOptions($optionsResolver);
        $this->options = $optionsResolver->resolve($options);
    }

    /**
     * {@inheritdoc}
     */
    public function isStored($path, $filter)
    {
        $cacheKey = $this->generateCacheKey($path, $filter);

        return
            $this->cache->contains($cacheKey) ||
            $this->resolver->isStored($path, $filter)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($path, $filter)
    {
        $key = $this->generateCacheKey($path, $filter);
        if ($this->cache->contains($key)) {
            return $this->cache->fetch($key);
        }

        $resolved = $this->resolver->resolve($path, $filter);

        $this->saveToCache($key, $resolved);

        return $resolved;
    }

    /**
     * {@inheritdoc}
     */
    public function store(BinaryInterface $binary, $path, $filter)
    {
        $this->resolver->store($binary, $path, $filter);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(array $paths, array $filters)
    {
        $this->resolver->remove($paths, $filters);

        foreach ($filters as $filter) {
            if (empty($paths)) {
                $this->removePathAndFilter(null, $filter);
            } else {
                foreach ($paths as $path) {
                    $this->removePathAndFilter($path, $filter);
                }
            }
        }
    }

    protected function removePathAndFilter($path, $filter)
    {
        $indexKey = $this->generateIndexKey($this->generateCacheKey($path, $filter));
        if (!$this->cache->contains($indexKey)) {
            return;
        }

        $index = $this->cache->fetch($indexKey);

        if (null === $path) {
            foreach ($index as $eachCacheKey) {
                $this->cache->delete($eachCacheKey);
            }

            $index = array();
        } else {
            $cacheKey = $this->generateCacheKey($path, $filter);
            if (false !== $indexIndex = array_search($cacheKey, $index)) {
                unset($index[$indexIndex]);
                $this->cache->delete($cacheKey);
            }
        }

        if (empty($index)) {
            $this->cache->delete($indexKey);
        } else {
            $this->cache->save($indexKey, $index);
        }
    }

    /**
     * Generate a unique cache key based on the given parameters.
     *
     * When overriding this method, ensure generateIndexKey is adjusted accordingly.
     *
     * @param string $path   The image path in use.
     * @param string $filter The filter in use.
     *
     * @return string
     */
    public function generateCacheKey($path, $filter)
    {
        return implode('.', array(
            $this->sanitizeCacheKeyPart($this->options['global_prefix']),
            $this->sanitizeCacheKeyPart($this->options['prefix']),
            $this->sanitizeCacheKeyPart($filter),
            $this->sanitizeCacheKeyPart($path),
        ));
    }

    /**
     * Generate the index key for the given cacheKey.
     *
     * The index contains a list of cache keys related to an image and a filter.
     *
     * @param string $cacheKey
     *
     * @return string
     */
    protected function generateIndexKey($cacheKey)
    {
        $cacheKeyStack = explode('.', $cacheKey);

        return implode('.', array(
            $this->sanitizeCacheKeyPart($this->options['global_prefix']),
            $this->sanitizeCacheKeyPart($this->options['prefix']),
            $this->sanitizeCacheKeyPart($this->options['index_key']),
            $this->sanitizeCacheKeyPart($cacheKeyStack[2]), // filter
        ));
    }

    /**
     * @param string $cacheKeyPart
     *
     * @return string
     */
    protected function sanitizeCacheKeyPart($cacheKeyPart)
    {
        return str_replace('.', '_', $cacheKeyPart);
    }

    /**
     * Save the given content to the cache and update the cache index.
     *
     * @param string $cacheKey
     * @param mixed  $content
     *
     * @return bool
     */
    protected function saveToCache($cacheKey, $content)
    {
        // Create or update the index list containing all cache keys for a given image and filter pairing.
        $indexKey = $this->generateIndexKey($cacheKey);
        if ($this->cache->contains($indexKey)) {
            $index = (array) $this->cache->fetch($indexKey);

            if (!in_array($cacheKey, $index)) {
                $index[] = $cacheKey;
            }
        } else {
            $index = array($cacheKey);
        }

        /*
         * Only save the content, if the index has been updated successfully.
         * This is required to have a (hopefully) synchron state between cache and backend.
         *
         * "Hopefully" because there are caches (like Memcache) which will remove keys by themselves.
         */
        if ($this->cache->save($indexKey, $index)) {
            return $this->cache->save($cacheKey, $content);
        }

        return false;
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'global_prefix' => 'liip_imagine.resolver_cache',
            'prefix' => get_class($this->resolver),
            'index_key' => 'index',
        ));

        $allowedTypesList = array(
          'global_prefix' => 'string',
          'prefix' => 'string',
          'index_key' => 'string',
        );

        if (version_compare(Kernel::VERSION_ID, '20600') >= 0) {
            foreach ($allowedTypesList as $option => $allowedTypes) {
                $resolver->setAllowedTypes($option, $allowedTypes);
            }
        } else {
            $resolver->setAllowedTypes($allowedTypesList);
        }
    }

    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }
}
