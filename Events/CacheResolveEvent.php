<?php

namespace Liip\ImagineBundle\Events;

use Symfony\Component\EventDispatcher\Event;

class CacheResolveEvent extends Event
{
    /**
     * Resource path.
     *
     * @var string
     */
    protected $path;

    /**
     * Filter name.
     *
     * @var string
     */
    protected $filter;

    /**
     * Resource url.
     *
     * @var null
     */
    protected $url;

    /**
     * Init default event state.
     *
     * @param string      $path
     * @param string      $filter
     * @param null|string $url
     */
    public function __construct($path, $filter, $url = null)
    {
        $this->path = $path;
        $this->filter = $filter;
        $this->url = $url;
    }

    /**
     * Sets resource path.
     *
     * @param $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Returns resource path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets filter name.
     *
     * @param $filter
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * Returns filter name.
     *
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Sets resource url.
     *
     * @param $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Returns resource url.
     */
    public function getUrl()
    {
        return $this->url;
    }
}
