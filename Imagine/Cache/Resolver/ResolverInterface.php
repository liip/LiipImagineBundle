<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Symfony\Component\HttpFoundation\Response;

interface ResolverInterface
{
    /**
     * Resolves filtered path for rendering in the browser.
     *
     * @param string $path   The path where the original file is expected to be.
     * @param string $filter The name of the imagine filter in effect.
     *
     * @return string|null An url of cached image
     */
    function resolve($path, $filter);

    /**
     * Stores the content of the given Response.
     *
     * @param Response $response The response provided by the _imagine_* filter route.
     * @param string   $path     The path where the original file is expected to be.
     * @param string   $filter   The name of the imagine filter in effect.
     *
     * @return Response The (modified) response to be sent to the browser.
     */
    function store(Response $response, $path, $filter);

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
