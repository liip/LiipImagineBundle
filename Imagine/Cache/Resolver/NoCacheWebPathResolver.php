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
    public function isStored($path, $filter, $runtimeConfigHash = null)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($path, $filter, $runtimeConfigHash = null)
    {
        if (null === $runtimeConfigHash) {
            return sprintf('%s://%s/%s',
                $this->requestContext->getScheme(),
                $this->requestContext->getHost(),
                ltrim($path, '/')
            );
        } else {
            return sprintf('%s://%s/%s/%s/%s',
                $this->requestContext->getScheme(),
                $this->requestContext->getHost(),
                'rc',
                $runtimeConfigHash,
                ltrim($path, '/')
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function store(BinaryInterface $binary, $path, $filter, $runtimeConfigHash = null)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function remove(array $paths, array $filters, $runtimeConfigHash = null)
    {
    }
}

