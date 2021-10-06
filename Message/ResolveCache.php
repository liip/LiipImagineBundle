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

/** @experimental */
class ResolveCache
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
     * @param string[]|null $filters
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
