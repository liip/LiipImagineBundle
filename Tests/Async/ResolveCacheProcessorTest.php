<?php

namespace Liip\ImagineBundle\Tests\Async;

use Enqueue\Bundle\EnqueueBundle;
use Enqueue\Client\ProducerInterface;
use Enqueue\Consumption\Result;
use Enqueue\Null\NullContext;
use Enqueue\Null\NullMessage;
use Liip\ImagineBundle\Async\CacheResolved;
use Liip\ImagineBundle\Async\ResolveCacheProcessor;
use Liip\ImagineBundle\Async\Topics;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;

class ResolveCacheProcessorTest extends AbstractTest
{
    public static function setUpBeforeClass()
    {
        if (!class_exists(EnqueueBundle::class)) {
            self::markTestSkipped('The tests are run without enqueue integration. Skip them');
        }
    }

    public function testShouldImplementProcessorInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Async\ResolveCacheProcessor');

        $this->assertTrue($rc->implementsInterface('Enqueue\Psr\PsrProcessor'));
    }

    public function testShouldImplementTopicSubscriberInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Async\ResolveCacheProcessor');

        $this->assertTrue($rc->implementsInterface('Enqueue\Client\TopicSubscriberInterface'));
    }

    public function testShouldImplementQueueSubscriberInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Async\ResolveCacheProcessor');

        $this->assertTrue($rc->implementsInterface('Enqueue\Consumption\QueueSubscriberInterface'));
    }

    public function testShouldSubscribeToExpectedTopic()
    {
        $topics = ResolveCacheProcessor::getSubscribedTopics();

        $this->assertInternalType('array', $topics);
        $this->assertArrayHasKey(Topics::RESOLVE_CACHE, $topics);
        $this->assertEquals(array(
            'queueName' => 'liip_imagine_resolve_cache',
            'queueNameHardcoded' => true,
        ), $topics[Topics::RESOLVE_CACHE]);
    }

    public function testShouldSubscribeToExpectedQueue()
    {
        $queues = ResolveCacheProcessor::getSubscribedQueues();

        $this->assertInternalType('array', $queues);
        $this->assertEquals(array('liip_imagine_resolve_cache'), $queues);
    }

    public function testCouldBeConstructedWithExpectedArguments()
    {
        new ResolveCacheProcessor(
            $this->createFilterManagerMock(),
            $this->createFilterServiceMock(),
            $this->createProducerMock()
        );
    }

    public function testShouldRejectMessagesWithInvalidJsonBody()
    {
        $processor = new ResolveCacheProcessor(
            $this->createFilterManagerMock(),
            $this->createFilterServiceMock(),
            $this->createProducerMock()
        );

        $message = new NullMessage();
        $message->setBody('[}');

        $result = $processor->process($message, new NullContext());

        $this->assertInstanceOf('Enqueue\Consumption\Result', $result);
        $this->assertEquals(Result::REJECT, (string) $result);
        $this->assertStringStartsWith('The malformed json given.', $result->getReason());
    }

    public function testShouldRejectMessagesWithoutPass()
    {
        $processor = new ResolveCacheProcessor(
            $this->createFilterManagerMock(),
            $this->createFilterServiceMock(),
            $this->createProducerMock()
        );

        $message = new NullMessage();
        $message->setBody('{}');

        $result = $processor->process($message, new NullContext());

        $this->assertInstanceOf('Enqueue\Consumption\Result', $result);
        $this->assertEquals(Result::REJECT, (string) $result);
        $this->assertEquals('The message does not contain "path" but it is required.', $result->getReason());
    }

    public function testShouldCreateFilteredImage()
    {
        $filterName = 'fooFilter';
        $imagePath = 'theImagePath';

        $filterManagerMock = $this->createFilterManagerMock();
        $filterManagerMock
            ->expects($this->once())
            ->method('getFilterConfiguration')
            ->willReturn(new FilterConfiguration(array(
                $filterName => array('fooFilterConfig'),
            )))
        ;

        $filterServiceMock = $this->createFilterServiceMock();
        $filterServiceMock
            ->expects($this->once())
            ->method('createFilteredImage')
            ->with($imagePath, $filterName);

        $processor = new ResolveCacheProcessor(
            $filterManagerMock,
            $filterServiceMock,
            $this->createProducerMock()
        );

        $message = new NullMessage();
        $message->setBody(json_encode(['path' => $imagePath]));

        $result = $processor->process($message, new NullContext());

        $this->assertEquals(Result::ACK, $result);
    }

    public function testShouldCreateOneImagePerFilter()
    {
        $filterName1 = 'fooFilter';
        $filterName2 = 'barFilter';
        $imagePath = 'theImagePath';

        $filterManagerMock = $this->createFilterManagerMock();
        $filterManagerMock
            ->expects($this->once())
            ->method('getFilterConfiguration')
            ->willReturn(new FilterConfiguration(array(
                $filterName1 => array('fooFilterConfig'),
                $filterName2 => array('fooFilterConfig'),
            )))
        ;

        $filterServiceMock = $this->createFilterServiceMock();
        $filterServiceMock
            ->expects($this->exactly(2))
            ->method('createFilteredImage')
            ->withConsecutive(
                [$imagePath, $filterName1],
                [$imagePath, $filterName2]
            );

        $processor = new ResolveCacheProcessor(
            $filterManagerMock,
            $filterServiceMock,
            $this->createProducerMock()
        );

        $message = new NullMessage();
        $message->setBody(json_encode(['path' => $imagePath]));

        $result = $processor->process($message, new NullContext());

        $this->assertEquals(Result::ACK, $result);
    }

    public function testShouldOnlyCreateImageForRequestedFilter()
    {
        $relevantFilter = 'fooFilter';
        $imagePath = 'theImagePath';

        $filterManagerMock = $this->createFilterManagerMock();
        $filterManagerMock
            ->expects($this->never())
            ->method('getFilterConfiguration');

        $filterServiceMock = $this->createFilterServiceMock();
        $filterServiceMock
            ->expects($this->once())
            ->method('createFilteredImage')
            ->with($imagePath, $relevantFilter);

        $processor = new ResolveCacheProcessor(
            $filterManagerMock,
            $filterServiceMock,
            $this->createProducerMock()
        );

        $message = new NullMessage();
        $message->setBody(json_encode(['path' => $imagePath, 'filters' => [$relevantFilter]]));

        $result = $processor->process($message, new NullContext());

        $this->assertEquals(Result::ACK, $result);
    }

    public function testShouldCreateOneImagePerRequestedFilter()
    {
        $relevantFilter1 = 'fooFilter';
        $relevantFilter2 = 'fooFilter';
        $imagePath = 'theImagePath';

        $filterManagerMock = $this->createFilterManagerMock();
        $filterManagerMock
            ->expects($this->never())
            ->method('getFilterConfiguration');

        $filterServiceMock = $this->createFilterServiceMock();
        $filterServiceMock
            ->expects($this->exactly(2))
            ->method('createFilteredImage')
            ->withConsecutive(
                [$imagePath, $relevantFilter1],
                [$imagePath, $relevantFilter2]
            );

        $processor = new ResolveCacheProcessor(
            $filterManagerMock,
            $filterServiceMock,
            $this->createProducerMock()
        );

        $message = new NullMessage();
        $message->setBody(json_encode(['path' => $imagePath, 'filters' => [$relevantFilter1, $relevantFilter2]]));

        $result = $processor->process($message, new NullContext());

        $this->assertEquals(Result::ACK, $result);
    }

    public function testShouldBurstCacheWhenResolvingForced()
    {
        $filterName = 'fooFilter';
        $imagePath = 'theImagePath';

        $filterManagerMock = $this->createFilterManagerMock();
        $filterManagerMock
            ->expects($this->once())
            ->method('getFilterConfiguration')
            ->willReturn(new FilterConfiguration(array(
                $filterName => array('fooFilterConfig'),
            )))
        ;

        $filterServiceMock = $this->createFilterServiceMock();
        $filterServiceMock
            ->expects($this->once())
            ->method('bustCache')
            ->with($imagePath, $filterName);

        $processor = new ResolveCacheProcessor(
            $filterManagerMock,
            $filterServiceMock,
            $this->createProducerMock()
        );

        $message = new NullMessage();
        $message->setBody(json_encode(['path' => $imagePath, 'force' => true]));

        $result = $processor->process($message, new NullContext());

        $this->assertEquals(Result::ACK, $result);
    }

    public function testShouldNotBurstCacheWhenResolvingNotForced()
    {
        $filterManagerMock = $this->createFilterManagerMock();
        $filterManagerMock
            ->expects($this->once())
            ->method('getFilterConfiguration')
            ->willReturn(new FilterConfiguration(array(
                'fooFilter' => array('fooFilterConfig'),
            )))
        ;

        $filterServiceMock = $this->createFilterServiceMock();
        $filterServiceMock
            ->expects($this->never())
            ->method('bustCache');

        $processor = new ResolveCacheProcessor(
            $filterManagerMock,
            $filterServiceMock,
            $this->createProducerMock()
        );

        $message = new NullMessage();
        $message->setBody(json_encode(['path' => 'theImagePath']));

        $result = $processor->process($message, new NullContext());

        $this->assertEquals(Result::ACK, $result);
    }

    public function testShouldSendMessageOnSuccessResolve()
    {
        $filterManagerMock = $this->createFilterManagerMock();
        $filterManagerMock
            ->expects($this->once())
            ->method('getFilterConfiguration')
            ->willReturn(new FilterConfiguration(array(
                'fooFilter' => array('fooFilterConfig'),
                'barFilter' => array('barFilterConfig'),
                'bazFilter' => array('bazFilterConfig'),
            )))
        ;

        $filterServiceMock = $this->createFilterServiceMock();
        $filterServiceMock
            ->expects($this->exactly(3))
            ->method('getUrlOfFilteredImage')
            ->willReturnCallback(function($path, $filter) {
                return $path.$filter.'Uri';
            });

        $producerMock = $this->createProducerMock();
        $producerMock
            ->expects($this->once())
            ->method('send')
            ->with(Topics::CACHE_RESOLVED, $this->isInstanceOf('Liip\ImagineBundle\Async\CacheResolved'))
        ->willReturnCallback(function ($topic, CacheResolved $message) {
            $this->assertEquals('theImagePath', $message->getPath());
            $this->assertEquals(array(
                'fooFilter' => 'theImagePathfooFilterUri',
                'barFilter' => 'theImagePathbarFilterUri',
                'bazFilter' => 'theImagePathbazFilterUri',
            ), $message->getUris());
        });

        $processor = new ResolveCacheProcessor(
            $filterManagerMock,
            $filterServiceMock,
            $producerMock
        );

        $message = new NullMessage();
        $message->setBody(json_encode(['path' => 'theImagePath']));

        $result = $processor->process($message, new NullContext());

        $this->assertEquals(Result::ACK, $result);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ProducerInterface
     */
    private function createProducerMock()
    {
        return $this->createMock(ProducerInterface::class);
    }
}
