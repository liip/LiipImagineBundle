<?php

namespace Liip\ImagineBundle\Async;

use Enqueue\Client\ProducerInterface;
use Enqueue\Client\TopicSubscriberInterface;
use Enqueue\Consumption\QueueSubscriberInterface;
use Enqueue\Consumption\Result;
use Enqueue\Psr\PsrContext;
use Enqueue\Psr\PsrMessage;
use Enqueue\Psr\PsrProcessor;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

class ResolveCacheProcessor implements PsrProcessor, TopicSubscriberInterface, QueueSubscriberInterface
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
        } catch (\Exception $e) {
            return Result::reject($e->getMessage());
        }

        $filters = $message->getFilters() ?: array_keys($this->filterManager->getFilterConfiguration()->all());
        $path = $message->getPath();
        $results = array();
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

        $this->producer->send(Topics::CACHE_RESOLVED, new CacheResolved($path, $results));

        return self::ACK;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return array(
            Topics::RESOLVE_CACHE => array('queueName' => Topics::RESOLVE_CACHE,  'queueNameHardcoded' => true),
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedQueues()
    {
        return array(Topics::RESOLVE_CACHE);
    }
}
