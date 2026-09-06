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

/**
 * Dispatched before and after cached images are removed from their cache resolvers.
 */
class CacheRemoveEvent extends Event
{
    /**
     * @var string[]
     */
    private $paths;

    /**
     * @var string[]
     */
    private $filters;

    /**
     * @param string[] $paths   the paths being removed, an empty list meaning every path of the given filters
     * @param string[] $filters the filter names the removal applies to
     */
    public function __construct(array $paths, array $filters)
    {
        $this->paths = $paths;
        $this->filters = $filters;
    }

    /**
     * @return string[]
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * @return string[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}
