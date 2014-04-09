<?php
namespace Liip\ImagineBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;

class CacheResolveEvent extends Event
{
    /**
     * @var \Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface
     */
    protected $resolver;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $filter;

    /**
     * @var string
     */
    protected $url;

    public function __construct(ResolverInterface $resolver, $path, $filter, $url = null)
    {
        $this->resolver = $resolver;
        $this->path = $path;
        $this->filter = $filter;
        $this->url = $url;
    }

    /**
     * @param ResolverInterface $resolver
     */
    public function setResolver(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @return ResolverInterface
     */
    public function getResolver()
    {
        return $this->resolver;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }
}
