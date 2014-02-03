<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException;

interface ResolverInterface
{
    /**
     * Checks whether the given path is stored within this Resolver.
     *
     * @param string $path
     * @param string $filter
     *
     * @return bool
     */
    function isStored($path, $filter);

    /**
     * Resolves filtered path for rendering in the browser.
     *
     * @param string $path   The path where the original file is expected to be.
     * @param string $filter The name of the imagine filter in effect.
     *
     * @return string The URL of the cached image.
     *
     * @throws NotResolvableException
     */
    function resolve($path, $filter);

    /**
     * Stores the content of the given binary.
     *
     * @param BinaryInterface $binary The image binary to store.
     * @param string          $path     The path where the original file is expected to be.
     * @param string          $filter   The name of the imagine filter in effect.
     *
     * @return void
     */
    function store(BinaryInterface $binary, $path, $filter);

    /**
     * Removes a stored image resource.
     *
     * @param string $path   The path where the original file is expected to be.
     * @param string $filter The name of the imagine filter in effect.
     *
     * @return bool Whether the file has been removed successfully.
     */
    function remove($path, $filter);

    /**
     * Clear the CacheResolver cache.
     *
     * @param string $cachePrefix The cache prefix as defined in the configuration.
     *
     * @return void
     */
    function clear($cachePrefix);
}
