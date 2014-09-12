<?php
namespace Liip\ImagineBundle\Events;

use Symfony\Component\EventDispatcher\Event;

class CacheResolveEvent extends Event
{
    /**
     * Resource path
     * @var string
     */
    protected $path;

    /**
     * Filter name
     * @var string
     */
    protected $filter;

    /**
     * RuntimeConfig
     *
     * @var
     */
    protected $runtimeConfig;

    /**
     * Resource url
     * @var null
     */
    protected $url;

    /**
     * Init default event state
     *
     * @param string $path
     * @param string $filter
     * @param array  $runtimeConfig
     * @param null|string $url
     */
    public function __construct($path, $filter, array $runtimeConfig = array(), $url = null)
    {
        $this->path = $path;
        $this->filter = $filter;
        $this->runtimeConfig = $runtimeConfig;
        $this->url = $url;
    }

    /**
     * Sets resource path
     *
     * @param $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Returns resource path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets filter name
     *
     * @param $filter
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * Returns filter name
     *
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Sets runtimeConfig
     *
     * @param array $runtimeConfig
     */
    public function setRuntimeConfig(array $runtimeConfig)
    {
        $this->runtimeConfig = $runtimeConfig;
    }

    /**
     * Returns runtimeConfig
     *
     * @return array
     */
    public function getRuntimeConfig()
    {
        return $this->runtimeConfig;
    }

    /**
     * Sets resource url
     *
     * @param $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Returns resource url
     *
     * @return null
     */
    public function getUrl()
    {
        return $this->url;
    }
}
