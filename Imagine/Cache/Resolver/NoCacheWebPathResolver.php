<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\SignerInterface;
use Symfony\Component\Routing\RequestContext;

class NoCacheWebPathResolver implements ResolverInterface
{
    /**
     * @var \Liip\ImagineBundle\Imagine\Cache\SignerInterface
     */
    protected $signer;

    /**
     * @param RequestContext $requestContext
     */
    public function __construct(RequestContext $requestContext, SignerInterface $signer)
    {
        $this->requestContext = $requestContext;
        $this->signer = $signer;
    }

    /**
     * {@inheritDoc}
     */
    public function isStored($path, $filter, array $runtimeConfig = array())
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($path, $filter, array $runtimeConfig = array())
    {
        if (empty($runtimeConfig)) {
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
                $this->signer->sign($path, $runtimeConfig),
                ltrim($path, '/')
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function store(BinaryInterface $binary, $path, $filter, array $runtimeConfig = array())
    {
    }

    /**
     * {@inheritDoc}
     */
    public function remove(array $paths, array $filters, array $runtimeConfig = array())
    {
    }
}

