<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Symfony\Component\Routing\RequestContext;

class NoCacheWebPathResolver implements ResolverInterface
{
    /**
     * @param RequestContext $requestContext
     */
    public function __construct(RequestContext $requestContext)
    {
        $this->requestContext = $requestContext;
    }

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
        return sprintf('%s://%s/%s',
            $this->requestContext->getScheme(),
            $this->requestContext->getHost(),
            ltrim($path, '/')
        );
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
    public function remove(array $paths, array $filters)
    {
    }
}
