<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Message\Handler;

use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Message\WarmupCache;
use Liip\ImagineBundle\Service\FilterService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Listen to WarmupCache messages and prepare the cache for those images.
 *
 * @experimental
 */
class WarmupCacheHandler implements MessageHandlerInterface
{
    /** @var FilterManager */
    private $filterManager;

    /** @var FilterService */
    private $filterService;

    public function __construct(FilterManager $filterManager, FilterService $filterService)
    {
        $this->filterManager = $filterManager;
        $this->filterService = $filterService;
    }

    public function __invoke(WarmupCache $message): void
    {
        $filters = $message->getFilters() ?: array_keys($this->filterManager->getFilterConfiguration()->all());
        $path = $message->getPath();

        foreach ($filters as $filter) {
            $this->filterService->warmUpCache($path, $filter, null, $message->isForce());

            $this->filterService->getUrlOfFilteredImage($path, $filter);
        }
    }
}
