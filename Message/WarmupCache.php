<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Message;

/**
 * Message to warm up the cache for an image.
 *
 * Your application needs to dispatch this message when it becomes aware of a new image.
 *
 * @experimental
 */
class WarmupCache
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string[]|null
     */
    private $filters;

    /**
     * @var bool
     */
    private $force;

    /**
     * @param string[]|null $filters List of filter set names to warm up. If not set, all available filter sets are warmed up
     * @param bool          $force   Whether to recreate existing cached images or only create them when no cache currently exists
     */
    public function __construct(string $path, ?array $filters = null, bool $force = false)
    {
        $this->path = $path;
        $this->filters = $filters;
        $this->force = $force;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string[]|null
     */
    public function getFilters(): ?array
    {
        return $this->filters;
    }

    public function isForce(): bool
    {
        return $this->force;
    }
}
