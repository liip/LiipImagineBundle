<?php
namespace Liip\ImagineBundle\Events;

use Symfony\Component\EventDispatcher\Event;

class CacheResolveEvent extends Event
{
    protected $path;

    protected $filter;

    protected $url;

    public function __construct($path, $filter, $url = null)
    {
        $this->path = $path;
        $this->filter = $filter;
        $this->url = $url;
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
