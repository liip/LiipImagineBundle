<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event as ContractsEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;

if (is_subclass_of(EventDispatcherInterface::class, ContractsEventDispatcherInterface::class)) {
    abstract class BCEvent extends ContractsEvent
    {
    }
} else {
    abstract class BCEvent extends Event
    {
    }
}

class CacheResolveEvent extends BCEvent
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
     * @param string|null $url
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
