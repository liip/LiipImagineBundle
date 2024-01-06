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
use Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * @covers \Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver
 */
class AwsS3ResolverTest extends AbstractTest
{
    public function testImplementsResolverInterface(): void
    {
        $rc = new \ReflectionClass(AwsS3Resolver::class);

        $this->assertTrue($rc->implementsInterface(ResolverInterface::class));
    }

    public function testNoDoubleSlashesInObjectUrlOnResolve(): void
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('getObjectUrl')
            ->with('images.example.com', 'thumb/some-folder/path.jpg')
            ->willReturn('http://images.example.com/some-folder/path.jpg');

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->resolve('/some-folder/path.jpg', 'thumb');
    }

    public function testObjUrlOptionsPassedToS3ClintOnResolve(): void
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('getObjectUrl')
            ->with('images.example.com', 'thumb/some-folder/path.jpg', 0, ['torrent' => true])
            ->willReturn('http://images.example.com/some-folder/path.jpg');

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->setGetOption('torrent', true);
        $resolver->resolve('/some-folder/path.jpg', 'thumb');
    }

    public function testLogNotCreatedObjects(): void
    {
        $this->expectException(\Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotStorableException::class);
        $this->expectExceptionMessage('The object could not be created on Amazon S3');

        $binary = new Binary('aContent', 'image/jpeg', 'jpeg');

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('putObject')
            ->will($this->throwException(new \Exception('Put object on amazon failed')));

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('error');

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->setLogger($logger);
        $resolver->store($binary, 'foobar.jpg', 'thumb');
    }

    public function testCreateObjectOnAmazon(): void
    {
        $binary = new Binary('aContent', 'image/jpeg', 'jpeg');

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('putObject');

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->store($binary, 'thumb/foobar.jpg', 'thumb');
    }

    public function testObjectOptionsPassedToS3ClintOnCreate(): void
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

    public function testIsStoredChecksObjectExistence(): void
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('doesObjectExist')
            ->willReturn(false);

        $resolver = new AwsS3Resolver($s3, 'images.example.com');

        $this->assertFalse($resolver->isStored('/some-folder/path.jpg', 'thumb'));
    }

    public function testReturnResolvedImageUrlOnResolve(): void
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

    public function testDoNothingIfFiltersAndPathsEmptyOnRemove(): void
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

    public function testRemoveCacheForPathAndFilterOnRemove(): void
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
            ]);

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->remove(['some-folder/path.jpg'], ['thumb']);
    }

    public function testRemoveCacheForSomePathsAndFilterOnRemove(): void
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->exactly(2))
            ->method('doesObjectExist')
            ->withConsecutive(
                ['images.example.com', 'thumb/pathOne.jpg'],
                ['images.example.com', 'thumb/pathTwo.jpg']
            )
            ->willReturn(true);
        $s3
            ->method('deleteObject')
            ->withConsecutive(
                [
                    'Bucket' => 'images.example.com',
                    'Key' => 'thumb/pathOne.jpg',
                ],
                [
                    'Bucket' => 'images.example.com',
                    'Key' => 'thumb/pathTwo.jpg',
                ]
            );

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->remove(
            ['pathOne.jpg', 'pathTwo.jpg'],
            ['thumb']
        );
    }

    public function testRemoveCacheForSomePathsAndSomeFiltersOnRemove(): void
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->exactly(4))
            ->method('doesObjectExist')
            ->withConsecutive(
                ['images.example.com', 'filterOne/pathOne.jpg'],
                ['images.example.com', 'filterOne/pathTwo.jpg'],
                ['images.example.com', 'filterTwo/pathOne.jpg'],
                ['images.example.com', 'filterTwo/pathTwo.jpg']
            )
            ->willReturn(true);
        $s3
            ->method('deleteObject')
            ->withConsecutive(
                [
                    'Bucket' => 'images.example.com',
                    'Key' => 'filterOne/pathOne.jpg',
                ],
                [
                    'Bucket' => 'images.example.com',
                    'Key' => 'filterOne/pathTwo.jpg',
                ],
                [
                    'Bucket' => 'images.example.com',
                    'Key' => 'filterTwo/pathOne.jpg',
                ],
                [
                    'Bucket' => 'images.example.com',
                    'Key' => 'filterTwo/pathTwo.jpg',
                ]
            );

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->remove(
            ['pathOne.jpg', 'pathTwo.jpg'],
            ['filterOne', 'filterTwo']
        );
    }

    public function testDoNothingWhenObjectNotExistForPathAndFilterOnRemove(): void
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

    public function testCatchAndLogExceptionsForPathAndFilterOnRemove(): void
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

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('error');

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->setLogger($logger);
        $resolver->remove(['some-folder/path.jpg'], ['thumb']);
    }

    public function testRemoveCacheForFilterOnRemove(): void
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

    public function testRemoveCacheForSomeFiltersOnRemove(): void
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

    public function testCatchAndLogExceptionForFilterOnRemove(): void
    {
        $expectedBucket = 'images.example.com';
        $expectedFilter = 'theFilter';

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('deleteMatchingObjects')
            ->will($this->throwException(new \Exception()));

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('error');

        $resolver = new AwsS3Resolver($s3, $expectedBucket);
        $resolver->setLogger($logger);
        $resolver->remove([], [$expectedFilter]);
    }

    /**
     * @return MockObject&\Aws\S3\S3Client
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
