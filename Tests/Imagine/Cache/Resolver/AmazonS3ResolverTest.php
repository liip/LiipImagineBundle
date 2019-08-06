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

use Liip\ImagineBundle\Imagine\Cache\Resolver\AmazonS3Resolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Cache\Resolver\AmazonS3Resolver
 */
class AmazonS3ResolverTest extends AbstractTest
{
    public function testImplementsResolverInterface()
    {
        $rc = new \ReflectionClass(AmazonS3Resolver::class);

        $this->assertTrue($rc->implementsInterface(ResolverInterface::class));
    }

    public function testNoDoubleSlashesInObjectUrlOnResolve()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('get_object_url')
            ->with('images.example.com', 'thumb/some-folder/path.jpg');

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->resolve('/some-folder/path.jpg', 'thumb');
    }

    public function testObjUrlOptionsPassedToAmazonOnResolve()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('get_object_url')
            ->with('images.example.com', 'thumb/some-folder/path.jpg', 0, ['torrent' => true]);

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->setObjectUrlOption('torrent', true);
        $resolver->resolve('/some-folder/path.jpg', 'thumb');
    }

    public function testThrowsAndLogIfCanNotCreateObjectOnAmazon()
    {
        $this->expectException(\Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotStorableException::class);
        $this->expectExceptionMessage('The object could not be created on Amazon S3');

        $binary = new Binary('aContent', 'image/jpeg', 'jpeg');

        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('create_object')
            ->willReturn($this->createCFResponseMock(false));

        $logger = $this->createLoggerInterfaceMock();
        $logger
            ->expects($this->once())
            ->method('error');

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->setLogger($logger);
        $resolver->store($binary, 'foobar.jpg', 'thumb');
    }

    public function testCreatedObjectOnAmazon()
    {
        $binary = new Binary('aContent', 'image/jpeg', 'jpeg');

        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('create_object')
            ->willReturn($this->createCFResponseMock(true));

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->store($binary, 'foobar.jpg', 'thumb');
    }

    public function testIsStoredChecksObjectExistence()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('if_object_exists')
            ->willReturn(false);

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $this->assertFalse($resolver->isStored('/some-folder/path.jpg', 'thumb'));
    }

    public function testReturnResolvedImageUrlOnResolve()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('get_object_url')
            ->with('images.example.com', 'thumb/some-folder/path.jpg', 0, [])
            ->willReturn('http://images.example.com/some-folder/path.jpg');

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $this->assertSame(
            'http://images.example.com/some-folder/path.jpg',
            $resolver->resolve('/some-folder/path.jpg', 'thumb')
        );
    }

    public function testDoNothingIfFiltersAndPathsEmptyOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->never())
            ->method('if_object_exists');
        $s3
            ->expects($this->never())
            ->method('delete_object');
        $s3
            ->expects($this->never())
            ->method('delete_all_objects');

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->remove([], []);
    }

    public function testRemoveCacheForPathAndFilterOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('if_object_exists')
            ->with('images.example.com', 'thumb/some-folder/path.jpg')
            ->willReturn(true);
        $s3
            ->expects($this->once())
            ->method('delete_object')
            ->with('images.example.com', 'thumb/some-folder/path.jpg')
            ->willReturn($this->createCFResponseMock(true));

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->remove(['some-folder/path.jpg'], ['thumb']);
    }

    public function testRemoveCacheForSomePathsAndFilterOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->at(0))
            ->method('if_object_exists')
            ->with('images.example.com', 'filter/pathOne.jpg')
            ->willReturn(true);
        $s3
            ->expects($this->at(1))
            ->method('delete_object')
            ->with('images.example.com', 'filter/pathOne.jpg')
            ->willReturn($this->createCFResponseMock(true));
        $s3
            ->expects($this->at(2))
            ->method('if_object_exists')
            ->with('images.example.com', 'filter/pathTwo.jpg')
            ->willReturn(true);
        $s3
            ->expects($this->at(3))
            ->method('delete_object')
            ->with('images.example.com', 'filter/pathTwo.jpg')
            ->willReturn($this->createCFResponseMock(true));

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->remove(['pathOne.jpg', 'pathTwo.jpg'], ['filter']);
    }

    public function testRemoveCacheForSomePathsAndSomeFiltersOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->at(0))
            ->method('if_object_exists')
            ->with('images.example.com', 'filterOne/pathOne.jpg')
            ->willReturn(true);
        $s3
            ->expects($this->at(1))
            ->method('delete_object')
            ->with('images.example.com', 'filterOne/pathOne.jpg')
            ->willReturn($this->createCFResponseMock(true));
        $s3
            ->expects($this->at(2))
            ->method('if_object_exists')
            ->with('images.example.com', 'filterOne/pathTwo.jpg')
            ->willReturn(true);
        $s3
            ->expects($this->at(3))
            ->method('delete_object')
            ->with('images.example.com', 'filterOne/pathTwo.jpg')
            ->willReturn($this->createCFResponseMock(true));
        $s3
            ->expects($this->at(4))
            ->method('if_object_exists')
            ->with('images.example.com', 'filterTwo/pathOne.jpg')
            ->willReturn(true);
        $s3
            ->expects($this->at(5))
            ->method('delete_object')
            ->with('images.example.com', 'filterTwo/pathOne.jpg')
            ->willReturn($this->createCFResponseMock(true));
        $s3
            ->expects($this->at(6))
            ->method('if_object_exists')
            ->with('images.example.com', 'filterTwo/pathTwo.jpg')
            ->willReturn(true);
        $s3
            ->expects($this->at(7))
            ->method('delete_object')
            ->with('images.example.com', 'filterTwo/pathTwo.jpg')
            ->willReturn($this->createCFResponseMock(true));

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->remove(
            ['pathOne.jpg', 'pathTwo.jpg'],
            ['filterOne', 'filterTwo']
        );
    }

    public function testDoNothingWhenObjectNotExistForPathAndFilterOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('if_object_exists')
            ->with('images.example.com', 'filter/path.jpg')
            ->willReturn(false);
        $s3
            ->expects($this->never())
            ->method('delete_object');

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->remove(['path.jpg'], ['filter']);
    }

    public function testLogIfNotDeletedForPathAndFilterOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('if_object_exists')
            ->with('images.example.com', 'filter/path.jpg')
            ->willReturn(true);
        $s3
            ->expects($this->once())
            ->method('delete_object')
            ->willReturn($this->createCFResponseMock(false));

        $logger = $this->createLoggerInterfaceMock();
        $logger
            ->expects($this->once())
            ->method('error');

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->setLogger($logger);
        $resolver->remove(['path.jpg'], ['filter']);
    }

    public function testRemoveCacheForFilterOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('delete_all_objects')
            ->with('images.example.com', '/filter/i')
            ->willReturn(true);

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->remove([], ['filter']);
    }

    public function testRemoveCacheForSomeFiltersOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('delete_all_objects')
            ->with('images.example.com', '/filterOne|filterTwo/i')
            ->willReturn(true);

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->remove([], ['filterOne', 'filterTwo']);
    }

    public function testLogIfBatchNotDeletedForFilterOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('delete_all_objects')
            ->with('images.example.com', '/filter/i')
            ->willReturn(false);

        $logger = $this->createLoggerInterfaceMock();
        $logger
            ->expects($this->once())
            ->method('error');

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->setLogger($logger);
        $resolver->remove([], ['filter']);
    }

    /**
     * @param bool $ok
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\CFResponse
     */
    protected function createCFResponseMock($ok = true)
    {
        $s3Response = $this->createObjectMock(\CFResponse::class, ['isOK'], false);
        $s3Response
            ->expects($this->once())
            ->method('isOK')
            ->willReturn($ok);

        return $s3Response;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\AmazonS3
     */
    protected function createAmazonS3Mock()
    {
        if (!class_exists(\AmazonS3::class)) {
            $this->markTestSkipped('Requires the amazonwebservices/aws-sdk-for-php package.');
        }

        return $this
            ->getMockBuilder(\AmazonS3::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'if_object_exists',
                'create_object',
                'get_object_url',
                'delete_object',
                'delete_all_objects',
                'authenticate',
            ])
            ->getMock();
    }
}
