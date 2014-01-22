<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Binary\BinaryInterface;

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
    public function store(BinaryInterface $binary, $path, $filter)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function remove($path, $filter)
    {
    }
}
