<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ProtocolResolver implements ResolverInterface
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var ResolverInterface
     */
    protected $httpResolver;

    /**
     * @var ResolverInterface
     */
    protected $httpsResolver;

    /**
     * Constructor.
     *
     * @param RequestStack      $requestStack
     * @param ResolverInterface $httpResolver
     * @param ResolverInterface $httpsResolver
     */
    public function __construct(RequestStack $requestStack, ResolverInterface $httpResolver, ResolverInterface $httpsResolver)
    {
        $this->requestStack  = $requestStack;
        $this->httpResolver  = $httpResolver;
        $this->httpsResolver = $httpsResolver;
    }

    /**
    * {@inheritDoc}
    */
    public function isStored($path, $filter)
    {
        return $this->isSecure() ?
            $this->httpsResolver->isStored($path, $filter) :
            $this->httpResolver->isStored($path, $filter)
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($path, $filter)
    {
        return $this->isSecure() ?
            $this->httpsResolver->resolve($path, $filter) :
            $this->httpResolver->resolve($path, $filter)
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function store(BinaryInterface $binary, $path, $filter)
    {
        $this->isSecure() ?
            $this->httpsResolver->store($binary, $path, $filter) :
            $this->httpResolver->store($binary, $path, $filter)
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function remove(array $paths, array $filters)
    {
        $this->isSecure() ?
            $this->httpsResolver->remove($paths, $filters) :
            $this->httpResolver->remove($paths, $filters)
        ;
    }

    /**
     * @return bool
     */
    private function isSecure()
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null == $request) {
            throw new \LogicException('The request was not defined.');
        }

        return $request->isSecure();
    }
}
