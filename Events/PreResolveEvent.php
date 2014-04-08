<?php
namespace Liip\ImagineBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;

class PreResolveEvent extends Event
{
    protected $resolver;

    protected $path;

    protected $filter;

    public function __construct(ResolverInterface $resolver, $path, $filter)
    {
        $this->path = $path;
        $this->filter = $filter;
    }

    public function getResolver()
    {
        return $this->path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getFilter()
    {
        return $this->filter;
    }
}