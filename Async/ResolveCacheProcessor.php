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
use Interop\Queue\PsrContext;
use Interop\Queue\PsrMessage;
use Interop\Queue\PsrProcessor;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Service\FilterService;

final class ResolveCacheProcessor implements PsrProcessor, CommandSubscriberInterface, QueueSubscriberInterface
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

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedCommand(): array
    {
        return [
            'processorName' => Commands::RESOLVE_CACHE,
            'queueName' => Commands::RESOLVE_CACHE,
            'queueNameHardcoded' => true,
            'exclusive' => true,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedQueues(): array
    {
        return [Commands::RESOLVE_CACHE];
    }
}
