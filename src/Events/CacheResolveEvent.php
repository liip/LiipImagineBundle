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

use Symfony\Contracts\EventDispatcher\Event;

class CacheResolveEvent extends Event
{
    /**
     * Resource path.
     */
    protected string $path;

    /**
     * Filter name.
     */
    protected string $filter;

    /**
     * Resource url.
     */
    protected ?string $url;

    /**
     * Init default event state.
     */
    public function __construct(string $path, string $filter, string $url = null)
    {
        $this->path = $path;
        $this->filter = $filter;
        $this->url = $url;
    }

    /**
     * Sets resource path.
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * Returns resource path.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Sets filter name.
     */
    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * Returns filter name.
     */
    public function getFilter(): string
    {
        return $this->filter;
    }

    /**
     * Sets resource url.
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    /**
     * Returns resource url.
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }
}
