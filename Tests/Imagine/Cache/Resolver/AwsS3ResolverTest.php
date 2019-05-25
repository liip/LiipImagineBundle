<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Aws\S3\S3Client;
use Guzzle\Service\Resource\Model;
use Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver
 */
class AwsS3ResolverTest extends AbstractTest
{
    public function testImplementsResolverInterface()
    {
        $rc = new \ReflectionClass(AwsS3Resolver::class);

        $this->assertTrue($rc->implementsInterface(ResolverInterface::class));
    }

    public function testNoDoubleSlashesInObjectUrlOnResolve()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('getObjectUrl')
            ->with('images.example.com', 'thumb/some-folder/path.jpg');

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->resolve('/some-folder/path.jpg', 'thumb');
    }

    public function testObjUrlOptionsPassedToS3ClintOnResolve()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('getObjectUrl')
            ->with('images.example.com', 'thumb/some-folder/path.jpg', 0, ['torrent' => true]);

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->setGetOption('torrent', true);
        $resolver->resolve('/some-folder/path.jpg', 'thumb');
    }

    public function testLogNotCreatedObjects()
    {
        $this->expectException(\Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotStorableException::class);
        $this->expectExceptionMessage('The object could not be created on Amazon S3');

        $binary = new Binary('aContent', 'image/jpeg', 'jpeg');

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('putObject')
            ->will($this->throwException(new \Exception('Put object on amazon failed')));

        $logger = $this->createLoggerInterfaceMock();
        $logger
            ->expects($this->once())
            ->method('error');

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->setLogger($logger);
        $resolver->store($binary, 'foobar.jpg', 'thumb');
    }

    public function testCreateObjectOnAmazon()
    {
        $binary = new Binary('aContent', 'image/jpeg', 'jpeg');

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('putObject')
            ->willReturn($this->getS3ResponseMock());

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->store($binary, 'thumb/foobar.jpg', 'thumb');
    }

    public function testObjectOptionsPassedToS3ClintOnCreate()
    {
        $binary = new Binary('aContent', 'image/jpeg', 'jpeg');

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('putObject')
            ->with([
                'CacheControl' => 'max-age=86400',
                'ACL' => 'public-read',
                'Bucket' => 'images.example.com',
                'Key' => 'filter/images/foobar.jpg',
                'Body' => 'aContent',
                'ContentType' => 'image/jpeg',
            ]);

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->setPutOption('CacheControl', 'max-age=86400');
        $resolver->store($binary, 'images/foobar.jpg', 'filter');
    }

    public function testIsStoredChecksObjectExistence()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('doesObjectExist')
            ->willReturn(false);

        $resolver = new AwsS3Resolver($s3, 'images.example.com');

        $this->assertFalse($resolver->isStored('/some-folder/path.jpg', 'thumb'));
    }

    public function testReturnResolvedImageUrlOnResolve()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('getObjectUrl')
            ->with('images.example.com', 'thumb/some-folder/path.jpg', 0, [])
            ->willReturn('http://images.example.com/some-folder/path.jpg');

        $resolver = new AwsS3Resolver($s3, 'images.example.com');

        $this->assertSame(
            'http://images.example.com/some-folder/path.jpg',
            $resolver->resolve('/some-folder/path.jpg', 'thumb')
        );
    }

    public function testDoNothingIfFiltersAndPathsEmptyOnRemove()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->never())
            ->method('doesObjectExist');
        $s3
            ->expects($this->never())
            ->method('deleteObject');
        $s3
            ->expects($this->never())
            ->method('deleteMatchingObjects');

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->remove([], []);
    }

    public function testRemoveCacheForPathAndFilterOnRemove()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('doesObjectExist')
            ->with('images.example.com', 'thumb/some-folder/path.jpg')
            ->willReturn(true);
        $s3
            ->expects($this->once())
            ->method('deleteObject')
            ->with([
                'Bucket' => 'images.example.com',
                'Key' => 'thumb/some-folder/path.jpg',
            ])
            ->willReturn($this->getS3ResponseMock());

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->remove(['some-folder/path.jpg'], ['thumb']);
    }

    public function testRemoveCacheForSomePathsAndFilterOnRemove()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->at(0))
            ->method('doesObjectExist')
            ->with('images.example.com', 'thumb/pathOne.jpg')
            ->willReturn(true);
        $s3
            ->expects($this->at(1))
            ->method('deleteObject')
            ->with([
                'Bucket' => 'images.example.com',
                'Key' => 'thumb/pathOne.jpg',
            ])
            ->willReturn($this->getS3ResponseMock());
        $s3
            ->expects($this->at(2))
            ->method('doesObjectExist')
            ->with('images.example.com', 'thumb/pathTwo.jpg')
            ->willReturn(true);
        $s3
            ->expects($this->at(3))
            ->method('deleteObject')
            ->with([
                'Bucket' => 'images.example.com',
                'Key' => 'thumb/pathTwo.jpg',
            ])
            ->willReturn($this->getS3ResponseMock());

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->remove(
            ['pathOne.jpg', 'pathTwo.jpg'],
            ['thumb']
        );
    }

    public function testRemoveCacheForSomePathsAndSomeFiltersOnRemove()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->at(0))
            ->method('doesObjectExist')
            ->with('images.example.com', 'filterOne/pathOne.jpg')
            ->willReturn(true);
        $s3
            ->expects($this->at(1))
            ->method('deleteObject')
            ->with([
                'Bucket' => 'images.example.com',
                'Key' => 'filterOne/pathOne.jpg',
            ])
            ->willReturn($this->getS3ResponseMock());
        $s3
            ->expects($this->at(2))
            ->method('doesObjectExist')
            ->with('images.example.com', 'filterOne/pathTwo.jpg')
            ->willReturn(true);
        $s3
            ->expects($this->at(3))
            ->method('deleteObject')
            ->with([
                'Bucket' => 'images.example.com',
                'Key' => 'filterOne/pathTwo.jpg',
            ])
            ->willReturn($this->getS3ResponseMock());
        $s3
            ->expects($this->at(4))
            ->method('doesObjectExist')
            ->with('images.example.com', 'filterTwo/pathOne.jpg')
            ->willReturn(true);
        $s3
            ->expects($this->at(5))
            ->method('deleteObject')
            ->with([
                'Bucket' => 'images.example.com',
                'Key' => 'filterTwo/pathOne.jpg',
            ])
            ->willReturn($this->getS3ResponseMock());
        $s3
            ->expects($this->at(6))
            ->method('doesObjectExist')
            ->with('images.example.com', 'filterTwo/pathTwo.jpg')
            ->willReturn(true);
        $s3
            ->expects($this->at(7))
            ->method('deleteObject')
            ->with([
                'Bucket' => 'images.example.com',
                'Key' => 'filterTwo/pathTwo.jpg',
            ])
            ->willReturn($this->getS3ResponseMock());

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->remove(
            ['pathOne.jpg', 'pathTwo.jpg'],
            ['filterOne', 'filterTwo']
        );
    }

    public function testDoNothingWhenObjectNotExistForPathAndFilterOnRemove()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('doesObjectExist')
            ->with('images.example.com', 'thumb/some-folder/path.jpg')
            ->willReturn(false);
        $s3
            ->expects($this->never())
            ->method('deleteObject');

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->remove(['some-folder/path.jpg'], ['thumb']);
    }

    public function testCatchAndLogExceptionsForPathAndFilterOnRemove()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('doesObjectExist')
            ->with('images.example.com', 'thumb/some-folder/path.jpg')
            ->willReturn(true);
        $s3
            ->expects($this->once())
            ->method('deleteObject')
            ->will($this->throwException(new \Exception()));

        $logger = $this->createLoggerInterfaceMock();
        $logger
            ->expects($this->once())
            ->method('error');

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->setLogger($logger);
        $resolver->remove(['some-folder/path.jpg'], ['thumb']);
    }

    public function testRemoveCacheForFilterOnRemove()
    {
        $expectedBucket = 'images.example.com';
        $expectedFilter = 'theFilter';

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('deleteMatchingObjects')
            ->with($expectedBucket, null, "/$expectedFilter/i");

        $resolver = new AwsS3Resolver($s3, $expectedBucket);
        $resolver->remove([], [$expectedFilter]);
    }

    public function testRemoveCacheForSomeFiltersOnRemove()
    {
        $expectedBucket = 'images.example.com';
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('deleteMatchingObjects')
            ->with($expectedBucket, null, "/{$expectedFilterOne}|{$expectedFilterTwo}/i");

        $resolver = new AwsS3Resolver($s3, $expectedBucket);
        $resolver->remove([], [$expectedFilterOne, $expectedFilterTwo]);
    }

    public function testCatchAndLogExceptionForFilterOnRemove()
    {
        $expectedBucket = 'images.example.com';
        $expectedFilter = 'theFilter';

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('deleteMatchingObjects')
            ->will($this->throwException(new \Exception()));

        $logger = $this->createLoggerInterfaceMock();
        $logger
            ->expects($this->once())
            ->method('error');

        $resolver = new AwsS3Resolver($s3, $expectedBucket);
        $resolver->setLogger($logger);
        $resolver->remove([], [$expectedFilter]);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Model
     */
    protected function getS3ResponseMock()
    {
        return $this->createObjectMock(Model::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Aws\S3\S3Client
     */
    protected function getS3ClientMock()
    {
        return $this
            ->getMockBuilder(S3Client::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'deleteObject',
                'deleteMatchingObjects',
                'createObject',
                'putObject',
                'doesObjectExist',
                'getObjectUrl',
            ])->getMock();
    }
}
