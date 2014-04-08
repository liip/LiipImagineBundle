<?php
namespace Liip\ImagineBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;

class PostResolveEvent extends Event
{
    protected $resolver;

    protected $path;

    protected $filter;

    protected $url;

    public function __construct(ResolverInterface $resolver, $path, $filter, $url)
    {
        $this->resolver = $resolver;
        $this->path = $path;
        $this->filter = $filter;
        $this->url = $url;
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

    public function getUrl()
    {
        return $this->url;
    }
}