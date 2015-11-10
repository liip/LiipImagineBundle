<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver
 */
class AwsS3ResolverTest extends AbstractTest
{
    public function testImplementsResolverInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver');

        $this->assertTrue($rc->implementsInterface('Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface'));
    }

    public function testNoDoubleSlashesInObjectUrlOnResolve()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('getObjectUrl')
            ->with('images.example.com', 'thumb/some-folder/path.jpg')
        ;

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->resolve('/some-folder/path.jpg', 'thumb');
    }

    public function testObjUrlOptionsPassedToS3ClintOnResolve()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('getObjectUrl')
            ->with('images.example.com', 'thumb/some-folder/path.jpg', 0, array('torrent' => true))
        ;

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->setObjectUrlOption('torrent', true);
        $resolver->resolve('/some-folder/path.jpg', 'thumb');
    }

    public function testLogNotCreatedObjects()
    {
        $binary = new Binary('aContent', 'image/jpeg', 'jpeg');

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('putObject')
            ->will($this->throwException(new \Exception('Put object on amazon failed')))
        ;

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger
            ->expects($this->once())
            ->method('error')
        ;

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->setLogger($logger);

        $this->setExpectedException(
            'Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotStorableException',
            'The object could not be created on Amazon S3.'
        );
        $resolver->store($binary, 'foobar.jpg', 'thumb');
    }

    public function testCreateObjectOnAmazon()
    {
        $binary = new Binary('aContent', 'image/jpeg', 'jpeg');

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('putObject')
            ->will($this->returnValue($this->getS3ResponseMock()))
        ;

        $resolver = new AwsS3Resolver($s3, 'images.example.com');

        $this->assertNull($resolver->store($binary, 'thumb/foobar.jpg', 'thumb'));
    }

    public function testObjectOptionsPassedToS3ClintOnCreate()
    {
        $binary = new Binary('aContent', 'image/jpeg', 'jpeg');

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('putObject')
            ->with(array(
                'CacheControl' => 'max-age=86400',
                'ACL' => 'public-read',
                'Bucket' => 'images.example.com',
                'Key' => 'filter/images/foobar.jpg',
                'Body' => 'aContent',
                'ContentType' => 'image/jpeg',
            ))
        ;

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
            ->will($this->returnValue(false))
        ;

        $resolver = new AwsS3Resolver($s3, 'images.example.com');

        $this->assertFalse($resolver->isStored('/some-folder/path.jpg', 'thumb'));
    }

    public function testReturnResolvedImageUrlOnResolve()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('getObjectUrl')
            ->with('images.example.com', 'thumb/some-folder/path.jpg', 0, array())
            ->will($this->returnValue('http://images.example.com/some-folder/path.jpg'))
        ;

        $resolver = new AwsS3Resolver($s3, 'images.example.com');

        $this->assertEquals(
            'http://images.example.com/some-folder/path.jpg',
            $resolver->resolve('/some-folder/path.jpg', 'thumb')
        );
    }

    public function testDoNothingIfFiltersAndPathsEmptyOnRemove()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->never())
            ->method('doesObjectExist')
        ;
        $s3
            ->expects($this->never())
            ->method('deleteObject')
        ;
        $s3
            ->expects($this->never())
            ->method('deleteMatchingObjects')
        ;

        $resolver = new AwsS3Resolver($s3, 'images.example.com');

        $resolver->remove(array(), array());
    }

    public function testRemoveCacheForPathAndFilterOnRemove()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('doesObjectExist')
            ->with('images.example.com', 'thumb/some-folder/path.jpg')
            ->will($this->returnValue(true))
        ;
        $s3
            ->expects($this->once())
            ->method('deleteObject')
            ->with(array(
                'Bucket' => 'images.example.com',
                'Key' => 'thumb/some-folder/path.jpg',
            ))
            ->will($this->returnValue($this->getS3ResponseMock(true)))
        ;

        $resolver = new AwsS3Resolver($s3, 'images.example.com');

        $resolver->remove(array('some-folder/path.jpg'), array('thumb'));
    }

    public function testRemoveCacheForSomePathsAndFilterOnRemove()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->at(0))
            ->method('doesObjectExist')
            ->with('images.example.com', 'thumb/pathOne.jpg')
            ->will($this->returnValue(true))
        ;
        $s3
            ->expects($this->at(1))
            ->method('deleteObject')
            ->with(array(
                'Bucket' => 'images.example.com',
                'Key' => 'thumb/pathOne.jpg',
            ))
            ->will($this->returnValue($this->getS3ResponseMock(true)))
        ;
        $s3
            ->expects($this->at(2))
            ->method('doesObjectExist')
            ->with('images.example.com', 'thumb/pathTwo.jpg')
            ->will($this->returnValue(true))
        ;
        $s3
            ->expects($this->at(3))
            ->method('deleteObject')
            ->with(array(
                'Bucket' => 'images.example.com',
                'Key' => 'thumb/pathTwo.jpg',
            ))
            ->will($this->returnValue($this->getS3ResponseMock(true)))
        ;

        $resolver = new AwsS3Resolver($s3, 'images.example.com');

        $resolver->remove(
            array('pathOne.jpg', 'pathTwo.jpg'),
            array('thumb')
        );
    }

    public function testRemoveCacheForSomePathsAndSomeFiltersOnRemove()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->at(0))
            ->method('doesObjectExist')
            ->with('images.example.com', 'filterOne/pathOne.jpg')
            ->will($this->returnValue(true))
        ;
        $s3
            ->expects($this->at(1))
            ->method('deleteObject')
            ->with(array(
                'Bucket' => 'images.example.com',
                'Key' => 'filterOne/pathOne.jpg',
            ))
            ->will($this->returnValue($this->getS3ResponseMock(true)))
        ;
        $s3
            ->expects($this->at(2))
            ->method('doesObjectExist')
            ->with('images.example.com', 'filterOne/pathTwo.jpg')
            ->will($this->returnValue(true))
        ;
        $s3
            ->expects($this->at(3))
            ->method('deleteObject')
            ->with(array(
                'Bucket' => 'images.example.com',
                'Key' => 'filterOne/pathTwo.jpg',
            ))
            ->will($this->returnValue($this->getS3ResponseMock(true)))
        ;
        $s3
            ->expects($this->at(4))
            ->method('doesObjectExist')
            ->with('images.example.com', 'filterTwo/pathOne.jpg')
            ->will($this->returnValue(true))
        ;
        $s3
            ->expects($this->at(5))
            ->method('deleteObject')
            ->with(array(
                'Bucket' => 'images.example.com',
                'Key' => 'filterTwo/pathOne.jpg',
            ))
            ->will($this->returnValue($this->getS3ResponseMock(true)))
        ;
        $s3
            ->expects($this->at(6))
            ->method('doesObjectExist')
            ->with('images.example.com', 'filterTwo/pathTwo.jpg')
            ->will($this->returnValue(true))
        ;
        $s3
            ->expects($this->at(7))
            ->method('deleteObject')
            ->with(array(
                'Bucket' => 'images.example.com',
                'Key' => 'filterTwo/pathTwo.jpg',
            ))
            ->will($this->returnValue($this->getS3ResponseMock(true)))
        ;

        $resolver = new AwsS3Resolver($s3, 'images.example.com');

        $resolver->remove(
            array('pathOne.jpg', 'pathTwo.jpg'),
            array('filterOne', 'filterTwo')
        );
    }

    public function testDoNothingWhenObjectNotExistForPathAndFilterOnRemove()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('doesObjectExist')
            ->with('images.example.com', 'thumb/some-folder/path.jpg')
            ->will($this->returnValue(false))
        ;
        $s3
            ->expects($this->never())
            ->method('deleteObject')
        ;

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->remove(array('some-folder/path.jpg'), array('thumb'));
    }

    public function testCatchAndLogExceptionsForPathAndFilterOnRemove()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('doesObjectExist')
            ->with('images.example.com', 'thumb/some-folder/path.jpg')
            ->will($this->returnValue(true))
        ;
        $s3
            ->expects($this->once())
            ->method('deleteObject')
            ->will($this->throwException(new \Exception()))
        ;

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger
            ->expects($this->once())
            ->method('error')
        ;

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->setLogger($logger);
        $resolver->remove(array('some-folder/path.jpg'), array('thumb'));
    }

    public function testRemoveCacheForFilterOnRemove()
    {
        $expectedBucket = 'images.example.com';
        $expectedFilter = 'theFilter';

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('deleteMatchingObjects')
            ->with($expectedBucket, null, "/$expectedFilter/i")
        ;

        $resolver = new AwsS3Resolver($s3, $expectedBucket);

        $resolver->remove(array(), array($expectedFilter));
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
            ->with($expectedBucket, null, "/{$expectedFilterOne}|{$expectedFilterTwo}/i")
        ;

        $resolver = new AwsS3Resolver($s3, $expectedBucket);

        $resolver->remove(array(), array($expectedFilterOne, $expectedFilterTwo));
    }

    public function testCatchAndLogExceptionForFilterOnRemove()
    {
        $expectedBucket = 'images.example.com';
        $expectedFilter = 'theFilter';

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('deleteMatchingObjects')
            ->will($this->throwException(new \Exception()))
        ;

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger
            ->expects($this->once())
            ->method('error')
        ;

        $resolver = new AwsS3Resolver($s3, $expectedBucket);
        $resolver->setLogger($logger);

        $resolver->remove(array(), array($expectedFilter));
    }

    protected function getS3ResponseMock($ok = true)
    {
        $s3Response = $this->getMock('Guzzle\Service\Resource\Model');

        return $s3Response;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Aws\S3\S3Client
     */
    protected function getS3ClientMock()
    {
        $mockedMethods = array(
            'deleteObject',
            'deleteMatchingObjects',
            'createObject',
            'putObject',
            'doesObjectExist',
            'getObjectUrl',
        );

        return $this->getMock('Aws\S3\S3Client', $mockedMethods, array(), '', false);
    }
}
