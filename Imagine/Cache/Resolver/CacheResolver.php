<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Doctrine\Common\Cache\Cache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
     * @param Cache $cache
     * @param ResolverInterface $cacheResolver
     * @param array $options
     * @param OptionsResolverInterface $optionsResolver
     */
    public function __construct(Cache $cache, ResolverInterface $cacheResolver, array $options = array(), OptionsResolverInterface $optionsResolver = null)
    {
        $this->cache = $cache;
        $this->resolver = $cacheResolver;

        if (null === $optionsResolver) {
            $optionsResolver = new OptionsResolver();
        }

        $this->setDefaultOptions($optionsResolver);
        $this->options = $optionsResolver->resolve($options);
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Request $request, $path, $filter)
    {
        $key = $this->generateCacheKey('resolve', $path, $filter);
        if ($this->cache->contains($key)) {
            return $this->cache->fetch($key);
        }

        $targetPath = $this->resolver->resolve($request, $path, $filter);
        $this->saveToCache($key, $targetPath);

        /*
         * The targetPath being a string will be forwarded to the ResolverInterface::store method.
         * As there is no way to reverse this operation by the interface, we store this information manually.
         *
         * If it's not a string, it's a Response it will be returned as it without calling the store method.
         */
        if (is_string($targetPath)) {
            $reverseKey = $this->generateCacheKey('reverse', $targetPath, $filter);
            $this->saveToCache($reverseKey, $path);
        }

        return $targetPath;
    }

    /**
     * {@inheritDoc}
     */
    public function store(Response $response, $targetPath, $filter)
    {
        return $this->resolver->store($response, $targetPath, $filter);
    }

    /**
     * {@inheritDoc}
     */
    public function getBrowserPath($path, $filter, $absolute = false)
    {
        $key = $this->generateCacheKey('getBrowserPath', $path, $filter, array(
            $absolute ? 'absolute' : 'relative',
        ));

        if ($this->cache->contains($key)) {
            return $this->cache->fetch($key);
        }

        $result = $this->resolver->getBrowserPath($path, $filter, $absolute);
        $this->saveToCache($key, $result);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function remove($targetPath, $filter)
    {
        $removed = $this->resolver->remove($targetPath, $filter);

        // If the resolver did not remove the content, we can leave the cache.
        if ($removed) {
            $reverseKey = $this->generateCacheKey('reverse', $targetPath, $filter);
            if ($this->cache->contains($reverseKey)) {
                $path = $this->cache->fetch($reverseKey);

                // The indexKey is not utilizing the method so the value is not important.
                $indexKey = $this->generateIndexKey($this->generateCacheKey(null, $path, $filter));

                // Retrieve the index and remove the content from the cache.
                $index = $this->cache->fetch($indexKey);
                foreach ($index as $eachCacheKey) {
                    $this->cache->delete($eachCacheKey);
                }

                // Remove the auxiliary keys.
                $this->cache->delete($indexKey);
                $this->cache->delete($reverseKey);
            }
        }

        return $removed;
    }

    /**
     * {@inheritDoc}
     */
    public function clear($cachePrefix)
    {
        // TODO: implement cache clearing
    }

    /**
     * Generate a unique cache key based on the given parameters.
     *
     * When overriding this method, ensure generateIndexKey is adjusted accordingly.
     *
     * @param string $method The cached method.
     * @param string $path The image path in use.
     * @param string $filter The filter in use.
     * @param array $suffixes An optional list of additional parameters to use to create the key.
     *
     * @return string
     */
    public function generateCacheKey($method, $path, $filter, array $suffixes = array())
    {
        $keyStack = array(
            $this->options['global_prefix'],
            $this->options['prefix'],
            $filter,
            $path,
            $method,
        );

        return implode('.', array_merge($keyStack, $suffixes));
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

        $indexKeyStack = array(
            $this->options['global_prefix'],
            $this->options['prefix'],
            $this->options['index_key'],
            $cacheKeyStack[2], // filter
            $cacheKeyStack[3], // path
        );

        return implode('.', $indexKeyStack);
    }

    /**
     * Save the given content to the cache and update the cache index.
     *
     * @param string $cacheKey
     * @param mixed $content
     *
     * @return bool
     */
    protected function saveToCache($cacheKey, $content)
    {
        // Create or update the index list containing all cache keys for a given image and filter pairing.
        $indexKey = $this->generateIndexKey($cacheKey);
        if ($this->cache->contains($indexKey)) {
            $index = $this->cache->fetch($indexKey);

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

    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'global_prefix' => 'liip_imagine.resolver_cache',
            'prefix' => get_class($this->resolver),
            'index_key' => 'index',
        ));

        $resolver->setAllowedTypes(array(
            'global_prefix' => 'string',
            'prefix' => 'string',
            'index_key' => 'string',
        ));
    }
}
