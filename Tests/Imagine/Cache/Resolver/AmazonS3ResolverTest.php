<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\Resolver\AmazonS3Resolver;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\AmazonS3Resolver
 */
class AmazonS3ResolverTest extends AbstractTest
{
    public function testImplementsResolverInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Imagine\Cache\Resolver\AmazonS3Resolver');

        $this->assertTrue($rc->implementsInterface('Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface'));
    }

    public function testNoDoubleSlashesInObjectUrlOnResolve()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('get_object_url')
            ->with('images.example.com', 'thumb/some-folder/path.jpg')
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->resolve('/some-folder/path.jpg', 'thumb');
    }

    public function testObjUrlOptionsPassedToAmazonOnResolve()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('get_object_url')
            ->with('images.example.com', 'thumb/some-folder/path.jpg', 0, array('torrent' => true))
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->setObjectUrlOption('torrent', true);
        $resolver->resolve('/some-folder/path.jpg', 'thumb');
    }

    public function testThrowsAndLogIfCanNotCreateObjectOnAmazon()
    {
        $binary = new Binary('aContent', 'image/jpeg', 'jpeg');

        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('create_object')
            ->will($this->returnValue($this->createCFResponseMock(false)))
        ;

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger
            ->expects($this->once())
            ->method('error')
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->setLogger($logger);

        $this->setExpectedException(
            'Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotStorableException',
            'The object could not be created on Amazon S3.'
        );
        $resolver->store($binary, 'foobar.jpg', 'thumb');
    }

    public function testCreatedObjectOnAmazon()
    {
        $binary = new Binary('aContent', 'image/jpeg', 'jpeg');

        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('create_object')
            ->will($this->returnValue($this->createCFResponseMock(true)))
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $resolver->store($binary, 'foobar.jpg', 'thumb');
    }

    public function testIsStoredChecksObjectExistence()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('if_object_exists')
            ->will($this->returnValue(false))
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $this->assertFalse($resolver->isStored('/some-folder/path.jpg', 'thumb'));
    }

    public function testReturnResolvedImageUrlOnResolve()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('get_object_url')
            ->with('images.example.com', 'thumb/some-folder/path.jpg', 0, array())
            ->will($this->returnValue('http://images.example.com/some-folder/path.jpg'))
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $this->assertEquals(
            'http://images.example.com/some-folder/path.jpg',
            $resolver->resolve('/some-folder/path.jpg', 'thumb')
        );
    }

    public function testDoNothingIfFiltersAndPathsEmptyOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->never())
            ->method('if_object_exists')
        ;
        $s3
            ->expects($this->never())
            ->method('delete_object')
        ;
        $s3
            ->expects($this->never())
            ->method('delete_all_objects')
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $resolver->remove(array(), array());
    }

    public function testRemoveCacheForPathAndFilterOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('if_object_exists')
            ->with('images.example.com', 'thumb/some-folder/path.jpg')
            ->will($this->returnValue(true))
        ;
        $s3
            ->expects($this->once())
            ->method('delete_object')
            ->with('images.example.com', 'thumb/some-folder/path.jpg')
            ->will($this->returnValue($this->createCFResponseMock(true)))
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $resolver->remove(array('some-folder/path.jpg'), array('thumb'));
    }

    public function testRemoveCacheForSomePathsAndFilterOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->at(0))
            ->method('if_object_exists')
            ->with('images.example.com', 'filter/pathOne.jpg')
            ->will($this->returnValue(true))
        ;
        $s3
            ->expects($this->at(1))
            ->method('delete_object')
            ->with('images.example.com', 'filter/pathOne.jpg')
            ->will($this->returnValue($this->createCFResponseMock(true)))
        ;
        $s3
            ->expects($this->at(2))
            ->method('if_object_exists')
            ->with('images.example.com', 'filter/pathTwo.jpg')
            ->will($this->returnValue(true))
        ;
        $s3
            ->expects($this->at(3))
            ->method('delete_object')
            ->with('images.example.com', 'filter/pathTwo.jpg')
            ->will($this->returnValue($this->createCFResponseMock(true)))
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $resolver->remove(array('pathOne.jpg', 'pathTwo.jpg'), array('filter'));
    }

    public function testRemoveCacheForSomePathsAndSomeFiltersOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->at(0))
            ->method('if_object_exists')
            ->with('images.example.com', 'filterOne/pathOne.jpg')
            ->will($this->returnValue(true))
        ;
        $s3
            ->expects($this->at(1))
            ->method('delete_object')
            ->with('images.example.com', 'filterOne/pathOne.jpg')
            ->will($this->returnValue($this->createCFResponseMock(true)))
        ;
        $s3
            ->expects($this->at(2))
            ->method('if_object_exists')
            ->with('images.example.com', 'filterOne/pathTwo.jpg')
            ->will($this->returnValue(true))
        ;
        $s3
            ->expects($this->at(3))
            ->method('delete_object')
            ->with('images.example.com', 'filterOne/pathTwo.jpg')
            ->will($this->returnValue($this->createCFResponseMock(true)))
        ;
        $s3
            ->expects($this->at(4))
            ->method('if_object_exists')
            ->with('images.example.com', 'filterTwo/pathOne.jpg')
            ->will($this->returnValue(true))
        ;
        $s3
            ->expects($this->at(5))
            ->method('delete_object')
            ->with('images.example.com', 'filterTwo/pathOne.jpg')
            ->will($this->returnValue($this->createCFResponseMock(true)))
        ;
        $s3
            ->expects($this->at(6))
            ->method('if_object_exists')
            ->with('images.example.com', 'filterTwo/pathTwo.jpg')
            ->will($this->returnValue(true))
        ;
        $s3
            ->expects($this->at(7))
            ->method('delete_object')
            ->with('images.example.com', 'filterTwo/pathTwo.jpg')
            ->will($this->returnValue($this->createCFResponseMock(true)))
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $resolver->remove(
            array('pathOne.jpg', 'pathTwo.jpg'),
            array('filterOne', 'filterTwo')
        );
    }

    public function testDoNothingWhenObjectNotExistForPathAndFilterOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('if_object_exists')
            ->with('images.example.com', 'filter/path.jpg')
            ->will($this->returnValue(false))
        ;
        $s3
            ->expects($this->never())
            ->method('delete_object')
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $resolver->remove(array('path.jpg'), array('filter'));
    }

    public function testLogIfNotDeletedForPathAndFilterOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('if_object_exists')
            ->with('images.example.com', 'filter/path.jpg')
            ->will($this->returnValue(true))
        ;
        $s3
            ->expects($this->once())
            ->method('delete_object')
            ->will($this->returnValue($this->createCFResponseMock(false)))
        ;

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger
            ->expects($this->once())
            ->method('error')
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->setLogger($logger);

        $resolver->remove(array('path.jpg'), array('filter'));
    }

    public function testRemoveCacheForFilterOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('delete_all_objects')
            ->with('images.example.com', '/filter/i')
            ->will($this->returnValue(true))
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $resolver->remove(array(), array('filter'));
    }

    public function testRemoveCacheForSomeFiltersOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('delete_all_objects')
            ->with('images.example.com', '/filterOne|filterTwo/i')
            ->will($this->returnValue(true))
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $resolver->remove(array(), array('filterOne', 'filterTwo'));
    }

    public function testLogIfBatchNotDeletedForFilterOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('delete_all_objects')
            ->with('images.example.com', '/filter/i')
            ->will($this->returnValue(false))
        ;

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger
            ->expects($this->once())
            ->method('error')
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->setLogger($logger);

        $resolver->remove(array(), array('filter'));
    }

    /**
     * @param bool $ok
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\CFResponse
     */
    protected function createCFResponseMock($ok = true)
    {
        $s3Response = $this->getMock('CFResponse', array('isOK'), array(), '', false);
        $s3Response
            ->expects($this->once())
            ->method('isOK')
            ->will($this->returnValue($ok))
        ;

        return $s3Response;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\AmazonS3
     */
    protected function createAmazonS3Mock()
    {
        $mockedMethods = array(
            'if_object_exists',
            'create_object',
            'get_object_url',
            'delete_object',
            'delete_all_objects',
            'authenticate',
        );

        return $this->getMock('AmazonS3', $mockedMethods, array(), '', false);
    }
}
