<?php

namespace Liip\ImagineBundle\Tests\Async;

use Enqueue\Bundle\EnqueueBundle;
use Enqueue\Client\ProducerInterface;
use Enqueue\Consumption\Result;
use Enqueue\Null\NullContext;
use Enqueue\Null\NullMessage;
use Liip\ImagineBundle\Async\CacheResolved;
use Liip\ImagineBundle\Async\Commands;
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

    public function testShouldImplementCommandSubscriberInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Async\ResolveCacheProcessor');

        $this->assertTrue($rc->implementsInterface('Enqueue\Client\CommandSubscriberInterface'));
    }

    public function testShouldImplementQueueSubscriberInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Async\ResolveCacheProcessor');

        $this->assertTrue($rc->implementsInterface('Enqueue\Consumption\QueueSubscriberInterface'));
    }

    public function testShouldSubscribeToExpectedCommand()
    {
        $command = ResolveCacheProcessor::getSubscribedCommand();

        $this->assertInternalType('array', $command);
        $this->assertEquals(array(
            'processorName' => Commands::RESOLVE_CACHE,
            'queueName' => Commands::RESOLVE_CACHE,
            'queueNameHardcoded' => true,
            'exclusive' => true,
        ), $command);
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
            $this->createCacheManagerMock(),
            $this->createFilterManagerMock(),
            $this->createDataManagerMock(),
            $this->createProducerMock()
        );
    }

    public function testShouldRejectMessagesWithInvalidJsonBody()
    {
        $processor = new ResolveCacheProcessor(
            $this->createCacheManagerMock(),
            $this->createFilterManagerMock(),
            $this->createDataManagerMock(),
            $this->createProducerMock()
        );

        $message = new NullMessage();
        $message->setBody('[}');

        $result = $processor->process($message, new NullContext());

        $this->assertInstanceOf('Enqueue\Consumption\Result', $result);
        $this->assertEquals(Result::REJECT, $result->getStatus());
        $this->assertStringStartsWith('The malformed json given.', $result->getReason());
    }

    public function testShouldSendFailedReplyOnException()
    {
        $processor = new ResolveCacheProcessor(
            $this->createCacheManagerMock(),
            $this->createFilterManagerMock(),
            $this->createDataManagerMock(),
            $this->createProducerMock()
        );

        $message = new NullMessage();
        $message->setBody('[}');

        $result = $processor->process($message, new NullContext());

        $this->assertInstanceOf('Enqueue\Consumption\Result', $result);
        $this->assertInstanceOf('Enqueue\Psr\PsrMessage', $result->getReply());
        $this->assertEquals(
            array(
                "status" => false,
                "exception" => "The malformed json given. Error 2 and message State mismatch (invalid or malformed JSON)"
            ),
            json_decode($result->getReply()->getBody(), true)
        );
    }

    public function testShouldRejectMessagesWithoutPass()
    {
        $processor = new ResolveCacheProcessor(
            $this->createCacheManagerMock(),
            $this->createFilterManagerMock(),
            $this->createDataManagerMock(),
            $this->createProducerMock()
        );

        $message = new NullMessage();
        $message->setBody('{}');

        $result = $processor->process($message, new NullContext());

        $this->assertInstanceOf('Enqueue\Consumption\Result', $result);
        $this->assertEquals(Result::REJECT, (string) $result);
        $this->assertEquals('The message does not contain "path" but it is required.', $result->getReason());
    }

    public function testShouldResolveCacheIfNotStored()
    {
        $originalBinary = $this->createDummyBinary();
        $filteredBinary = $this->createDummyBinary();

        $filterManagerMock = $this->createFilterManagerMock();
        $filterManagerMock
            ->expects($this->once())
            ->method('getFilterConfiguration')
            ->willReturn(new FilterConfiguration(array(
                'fooFilter' => array('fooFilterConfig'),
            )))
        ;
        $filterManagerMock
            ->expects($this->once())
            ->method('applyFilter')
            ->with($this->identicalTo($originalBinary), 'fooFilter')
            ->willReturn($filteredBinary)
        ;

        $cacheManagerMock = $this->createCacheManagerMock();
        $cacheManagerMock
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->willReturn(false)
        ;
        $cacheManagerMock
            ->expects($this->once())
            ->method('store')
            ->with(
                $this->identicalTo($filteredBinary),
                'theImagePath',
                'fooFilter'
            )
        ;
        $cacheManagerMock
            ->expects($this->once())
            ->method('resolve')
            ->with('theImagePath', 'fooFilter')
        ;

        $dataManagerMock = $this->createDataManagerMock();
        $dataManagerMock
            ->expects($this->once())
            ->method('find')
            ->with('fooFilter', 'theImagePath')
            ->willReturn($originalBinary)
        ;

        $processor = new ResolveCacheProcessor(
            $cacheManagerMock,
            $filterManagerMock,
            $dataManagerMock,
            $this->createProducerMock()
        );

        $message = new NullMessage();
        $message->setBody('{"path": "theImagePath"}');

        $result = $processor->process($message, new NullContext());

        $this->assertInstanceOf('Enqueue\Consumption\Result', $result);
        $this->assertEquals(Result::ACK, (string) $result);
    }

    public function testShouldNotResolveCacheIfStoredAndNotForce()
    {
        $filterManagerMock = $this->createFilterManagerMock();
        $filterManagerMock
            ->expects($this->once())
            ->method('getFilterConfiguration')
            ->willReturn(new FilterConfiguration(array(
                'fooFilter' => array('fooFilterConfig'),
            )))
        ;
        $filterManagerMock
            ->expects($this->never())
            ->method('applyFilter')
        ;

        $cacheManagerMock = $this->createCacheManagerMock();
        $cacheManagerMock
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->willReturn(true)
        ;
        $cacheManagerMock
            ->expects($this->never())
            ->method('store')
        ;
        $cacheManagerMock
            ->expects($this->once())
            ->method('resolve')
            ->with('theImagePath', 'fooFilter')
            ->willReturn('fooFilterUri')
        ;

        $dataManagerMock = $this->createDataManagerMock();
        $dataManagerMock
            ->expects($this->never())
            ->method('find')
        ;

        $processor = new ResolveCacheProcessor(
            $cacheManagerMock,
            $filterManagerMock,
            $dataManagerMock,
            $this->createProducerMock()
        );

        $message = new NullMessage();
        $message->setBody('{"path": "theImagePath"}');

        $result = $processor->process($message, new NullContext());

        $this->assertInstanceOf('Enqueue\Consumption\Result', $result);
        $this->assertEquals(Result::ACK, (string) $result);
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
        $filterManagerMock
            ->expects($this->atLeastOnce())
            ->method('applyFilter')
            ->willReturn($this->createDummyBinary())
        ;

        $cacheManagerMock = $this->createCacheManagerMock();
        $cacheManagerMock
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->willReturn(false)
        ;
        $cacheManagerMock
            ->expects($this->atLeastOnce())
            ->method('store')
        ;
        $cacheManagerMock
            ->expects($this->atLeastOnce())
            ->method('resolve')
            ->willReturnCallback(function ($path, $filter) {
                return $path.$filter.'Uri';
            })
        ;

        $dataManagerMock = $this->createDataManagerMock();
        $dataManagerMock
            ->expects($this->atLeastOnce())
            ->method('find')
            ->willReturn($this->createDummyBinary())
        ;

        $testCase = $this;
        $producerMock = $this->createProducerMock();
        $producerMock
            ->expects($this->once())
            ->method('sendEvent')
            ->with(Topics::CACHE_RESOLVED, $this->isInstanceOf('Liip\ImagineBundle\Async\CacheResolved'))
        ->willReturnCallback(function ($topic, CacheResolved $message) use ($testCase) {
            $testCase->assertEquals('theImagePath', $message->getPath());
            $testCase->assertEquals(array(
                'fooFilter' => 'theImagePathfooFilterUri',
                'barFilter' => 'theImagePathbarFilterUri',
                'bazFilter' => 'theImagePathbazFilterUri',
            ), $message->getUris());
        });

        $processor = new ResolveCacheProcessor(
            $cacheManagerMock,
            $filterManagerMock,
            $dataManagerMock,
            $producerMock
        );

        $message = new NullMessage();
        $message->setBody('{"path": "theImagePath"}');

        $result = $processor->process($message, new NullContext());

        $this->assertInstanceOf('Enqueue\Consumption\Result', $result);
        $this->assertEquals(Result::ACK, (string) $result);
    }

    public function testShouldReturnReplyOnSuccessResolve()
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
        $filterManagerMock
            ->expects($this->atLeastOnce())
            ->method('applyFilter')
            ->willReturn($this->createDummyBinary())
        ;

        $cacheManagerMock = $this->createCacheManagerMock();
        $cacheManagerMock
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->willReturn(false)
        ;
        $cacheManagerMock
            ->expects($this->atLeastOnce())
            ->method('store')
        ;
        $cacheManagerMock
            ->expects($this->atLeastOnce())
            ->method('resolve')
            ->willReturnCallback(function ($path, $filter) {
                return $path.$filter.'Uri';
            })
        ;

        $dataManagerMock = $this->createDataManagerMock();
        $dataManagerMock
            ->expects($this->atLeastOnce())
            ->method('find')
            ->willReturn($this->createDummyBinary())
        ;

        $processor = new ResolveCacheProcessor(
            $cacheManagerMock,
            $filterManagerMock,
            $dataManagerMock,
            $this->createProducerMock()
        );

        $message = new NullMessage();
        $message->setBody('{"path": "theImagePath"}');

        $result = $processor->process($message, new NullContext());

        $this->assertInstanceOf('Enqueue\Consumption\Result', $result);
        $this->assertInstanceOf('Enqueue\Psr\PsrMessage', $result->getReply());
        $this->assertEquals(
            array(
                "status" => true,
                "results" => array(
                    'fooFilter' => 'theImagePathfooFilterUri',
                    'barFilter' => 'theImagePathbarFilterUri',
                    'bazFilter' => 'theImagePathbazFilterUri',
                )
            ),
            json_decode($result->getReply()->getBody(), true)
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ProducerInterface
     */
    private function createProducerMock()
    {
        return $this->createMock(ProducerInterface::class);
    }

    /**
     * @return Binary
     */
    private function createDummyBinary()
    {
        return new Binary('theContent', 'image/png', 'png');
    }
}
