<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

interface ResolverInterface
{
    /**
     * Gets filtered path for rendering in the browser
     *
     * @param string $path
     * @param string $filter
     * @param boolean $absolute
     *
     * @return string
     */
    function getBrowserPath($targetPath, $filter, $absolute = false);

    /**
     * Resolves filtered path for rendering in the browser
     *
     * @param Request $request
     * @param string $path
     * @param string $filter
     *
     * @return string
     */
    function resolve(Request $request, $targetPath, $filter);

    /**
     * @throws \RuntimeException
     * @param Response $response
     * @param string $targetPath
     * 
     * @return Response
     */
    function store(Response $response, $targetPath);
}
