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

use Enqueue\Client\CommandSubscriberInterface;
use Enqueue\Client\ProducerInterface;
use Enqueue\Consumption\QueueSubscriberInterface;
use Enqueue\Consumption\Result;
use Enqueue\Util\JSON;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Service\FilterService;

final class ResolveCacheProcessor implements Processor, CommandSubscriberInterface, QueueSubscriberInterface
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

    public function __construct(
        FilterManager $filterManager,
        FilterService $filterService,
        ProducerInterface $producer
    ) {
        $this->filterManager = $filterManager;
        $this->filterService = $filterService;
        $this->producer = $producer;
    }

    public function process(Message $psrMessage, Context $psrContext)
    {
        try {
            $message = ResolveCache::jsonDeserialize($psrMessage->getBody());

            $filters = $message->getFilters() ?: array_keys($this->filterManager->getFilterConfiguration()->all());
            $path = $message->getPath();
            $results = [];
            foreach ($filters as $filter) {
                if ($message->isForce()) {
                    $this->filterService->bustCache($path, $filter);
                }

                $results[$filter] = $this->filterService->getUrlOfFilteredImage($path, $filter);
            }

            $this->producer->sendEvent(Topics::CACHE_RESOLVED, new CacheResolved($path, $results));

            return Result::reply($psrContext->createMessage(JSON::encode([
                'status' => true,
                'results' => $results,
            ])));
        } catch (\Exception $e) {
            return Result::reply($psrContext->createMessage(JSON::encode([
                'status' => false,
                'exception' => $e->getMessage(),
            ])), Result::REJECT, $e->getMessage());
        }
    }

    public static function getSubscribedCommand(): array
    {
        return [
            'command' => Commands::RESOLVE_CACHE,
            'queue' => Commands::RESOLVE_CACHE,
            'prefix_queue' => false,
            'exclusive' => true,
        ];
    }

    public static function getSubscribedQueues(): array
    {
        return [Commands::RESOLVE_CACHE];
    }
}
