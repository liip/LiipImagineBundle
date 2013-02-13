<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * NoCacheResolver.
 */
class NoCacheResolver extends WebPathResolver
{
    /**
     * {@inheritDoc}
     */
    public function resolve(Request $request, $path, $filter)
    {
        $this->setBasePath($request->getBaseUrl());

        return $this->getFilePath($path, $filter);
    }

    /**
     * {@inheritDoc}
     */
    public function store(Response $response, $targetPath, $filter)
    {
        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function remove($targetPath, $filter)
    {
        return true;
    }
}
