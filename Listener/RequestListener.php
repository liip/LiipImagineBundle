<?php

namespace Avalanche\Bundle\ImagineBundle\Listener;

use Avalanche\Bundle\ImagineBundle\Imagine\CachePathResolver;
use Symfony\Component\EventDispatcher\EventInterface;
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
     * @param Symfony\Component\EventDispatcher\EventInterface $event
     */
    public function handle(EventInterface $event)
    {
        if ($event->get('request_type') !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->get('request');

        $this->resolver->setBasePath($request->getBasePath());
        $this->resolver->setBaseUrl($request->getBaseUrl());
    }
}
