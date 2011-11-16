<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

interface ResolverInterface
{
    /**
     * Resolves filtered path for rendering in the browser
     *
     * @param Request $request
     * @param string $path
     * @param string $filter
     *
     * @return string target path
     */
    function resolve(Request $request, $path, $filter);

    /**
     * @param Response $response
     * @param string $targetPath
     * @param string $filter
     *
     * @return Response
     */
    function store(Response $response, $targetPath, $filter);
}
