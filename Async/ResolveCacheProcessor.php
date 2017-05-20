<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Async;

use Enqueue\Client\ProducerInterface;
use Enqueue\Client\TopicSubscriberInterface;
use Enqueue\Consumption\QueueSubscriberInterface;
use Enqueue\Consumption\Result;
use Enqueue\Psr\PsrContext;
use Enqueue\Psr\PsrMessage;
use Enqueue\Psr\PsrProcessor;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Service\FilterService;

class ResolveCacheProcessor implements PsrProcessor, TopicSubscriberInterface, QueueSubscriberInterface
{
    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @var FilterService
     */
    private $filterService;

    /**
     * @var ProducerInterface
     */
    private $producer;

    /**
     * @param FilterManager     $filterManager
     * @param FilterService     $filterService
     * @param ProducerInterface $producer
     */
    public function __construct(
        FilterManager $filterManager,
        FilterService $filterService,
        ProducerInterface $producer
    ) {
        $this->filterManager = $filterManager;
        $this->filterService = $filterService;
        $this->producer = $producer;
    }

    /**
     * {@inheritdoc}
     */
    public function process(PsrMessage $psrMessage, PsrContext $psrContext)
    {
        try {
            $message = ResolveCache::jsonDeserialize($psrMessage->getBody());
        } catch (\Exception $e) {
            return Result::reject($e->getMessage());
        }

        $filters = $message->getFilters() ?: array_keys($this->filterManager->getFilterConfiguration()->all());
        $path = $message->getPath();
        $results = [];
        foreach ($filters as $filter) {
            if ($message->isForce()) {
                $this->filterService->bustCache($path, $filter);
            }

            $results[$filter] = $this->filterService->getUrlOfFilteredImage($path, $filter);
        }

        $this->producer->send(Topics::CACHE_RESOLVED, new CacheResolved($path, $results));

        return self::ACK;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics(): array
    {
        return [
            Topics::RESOLVE_CACHE => ['queueName' => Topics::RESOLVE_CACHE,  'queueNameHardcoded' => true],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedQueues(): array
    {
        return [Topics::RESOLVE_CACHE];
    }
}
