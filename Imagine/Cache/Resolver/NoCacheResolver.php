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
    public function isStored($path, $filter)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($path, $filter)
    {
        return $this->getRequest()->getSchemeAndHttpHost().'/'.$path;
    }

    /**
     * {@inheritDoc}
     */
    public function store(Response $response, $path, $filter)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function remove($path, $filter)
    {
        return true;
    }
}
