<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Symfony\Component\HttpFoundation\Response;

/**
 * NoCacheResolver.
 */
class NoCacheResolver extends WebPathResolver
{
    /**
     * {@inheritDoc}
     */
    public function resolve($path, $filter)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function store(Response $response, $path, $filter)
    {
        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function remove($path, $filter)
    {
        return true;
    }
}
