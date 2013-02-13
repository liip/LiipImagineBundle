<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ResolverInterface
{
    /**
     * Resolves filtered path for rendering in the browser.
     *
     * @param Request $request The request made against a _imagine_* filter route.
     * @param string $path The path where the resolved file is expected.
     * @param string $filter The name of the imagine filter in effect.
     *
     * @return string|Response The target path to be used in other methods of this Resolver,
     *                         a Response may be returned to avoid calling store upon resolution.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException In case the path can not be resolved.
     */
    function resolve(Request $request, $path, $filter);

    /**
     * Stores the content of the given Response.
     *
     * @param Response $response The response provided by the _imagine_* filter route.
     * @param string $targetPath The target path provided by the resolve method.
     * @param string $filter The name of the imagine filter in effect.
     *
     * @return Response The (modified) response to be sent to the browser.
     */
    function store(Response $response, $targetPath, $filter);

    /**
     * Returns a web accessible URL.
     *
     * @param string $path The path where the resolved file is expected.
     * @param string $filter The name of the imagine filter in effect.
     * @param bool $absolute Whether to generate an absolute URL or a relative path is accepted.
     *                       In case the resolver does not support relative paths, it may ignore this flag.
     *
     * @return string
     */
    function getBrowserPath($path, $filter, $absolute = false);

    /**
     * Removes a stored image resource.
     *
     * @param string $targetPath The target path provided by the resolve method.
     * @param string $filter The name of the imagine filter in effect.
     *
     * @return bool Whether the file has been removed successfully.
     */
    function remove($targetPath, $filter);

    /**
     * Clear the CacheResolver cache.
     *
     * @param string $cachePrefix The cache prefix as defined in the configuration.
     *
     * @return void
     */
    function clear($cachePrefix);
}
