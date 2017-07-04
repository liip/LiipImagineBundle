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
use Enqueue\Psr\PsrContext;
use Enqueue\Psr\PsrMessage;
use Enqueue\Psr\PsrProcessor;
use Enqueue\Util\JSON;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

class ResolveCacheProcessor implements PsrProcessor, CommandSubscriberInterface, QueueSubscriberInterface
{
    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @var DataManager
     */
    private $dataManager;

    /**
     * @var ProducerInterface
     */
    private $producer;

    /**
     * @param CacheManager      $cacheManager
     * @param FilterManager     $filterManager
     * @param DataManager       $dataManager
     * @param ProducerInterface $producer
     */
    public function __construct(
        CacheManager $cacheManager,
        FilterManager $filterManager,
        DataManager $dataManager,
        ProducerInterface $producer
    ) {
        $this->cacheManager = $cacheManager;
        $this->filterManager = $filterManager;
        $this->dataManager = $dataManager;
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
                if ($this->cacheManager->isStored($path, $filter) && $message->isForce()) {
                    $this->cacheManager->remove($path, $filter);
                }

                if (false == $this->cacheManager->isStored($path, $filter)) {
                    $binary = $this->dataManager->find($filter, $path);
                    $this->cacheManager->store(
                        $this->filterManager->applyFilter($binary, $filter),
                        $path,
                        $filter
                    );
                }

                $results[$filter] = $this->cacheManager->resolve($path, $filter);
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
