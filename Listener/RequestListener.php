<?php

namespace Avalanche\Bundle\ImagineBundle\Listener;

use Avalanche\Bundle\ImagineBundle\Imagine\CachePathResolver;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestListener
{
    /**
     * @var Avalanche\Bundle\ImagineBundle\Imagine\CachePathResolver
     */
    private $resolver;

    /**
     * @param Avalanche\Bundle\ImagineBundle\Imagine\CachePathResolver $resolver
     */
    public function __construct(CachePathResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @param Symfony\Component\HttpKernel\Event\GetResponseEvent
     */
    public function onCoreRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();

        $this->resolver->setBasePath($request->getBasePath());
        $this->resolver->setBaseUrl($request->getBaseUrl());
    }
    
}
