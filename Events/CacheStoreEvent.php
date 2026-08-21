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

use Liip\ImagineBundle\Binary\BinaryInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched before and after a filtered image is handed over to its cache resolver.
 */
class CacheStoreEvent extends Event
{
    /**
     * @var BinaryInterface
     */
    private $binary;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $filter;

    /**
     * @var string|null
     */
    private $resolver;

    /**
     * @param string      $path     the path the filtered image is stored under
     * @param string      $filter   the name of the imagine filter in effect
     * @param string|null $resolver the resolver name passed to the cache manager, null when the filter default is used
     */
    public function __construct(BinaryInterface $binary, string $path, string $filter, ?string $resolver = null)
    {
        $this->binary = $binary;
        $this->path = $path;
        $this->filter = $filter;
        $this->resolver = $resolver;
    }

    public function getBinary(): BinaryInterface
    {
        return $this->binary;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getFilter(): string
    {
        return $this->filter;
    }

    public function getResolver(): ?string
    {
        return $this->resolver;
    }
}
