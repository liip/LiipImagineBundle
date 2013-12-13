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
     * @return Response An HTTP response that either contains image content or redirects to a URL to load the image from.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException In case the path can not be resolved.
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
     * Returns a web accessible URL.
     *
     * @param string $path   The path where the original file is expected to be.
     * @param string $filter The name of the imagine filter in effect.
     * @param bool $absolute Whether to generate an absolute URL or a relative path is accepted.
     *                       In case the resolver does not support relative paths, it may ignore this flag.
     *
     * @return string
     */
    function getBrowserPath($path, $filter, $absolute = false);

    /**
     * Removes a cached image resource.
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
