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
    protected string $path;

    protected string $filter;

    protected ?string $url;

    public function __construct(string $path, string $filter, ?string $url = null)
    {
        $this->path = $path;
        $this->filter = $filter;
        $this->url = $url;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    public function getFilter(): string
    {
        return $this->filter;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
