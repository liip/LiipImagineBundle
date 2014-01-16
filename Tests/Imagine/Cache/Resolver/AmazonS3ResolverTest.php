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

    public function testImplementsLoggerAwareInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Imagine\Cache\Resolver\AmazonS3Resolver');

        $this->assertTrue($rc->implementsInterface('Psr\Log\LoggerAwareInterface'));
    }

    public function testSetNullLoggerInConstructor()
    {
        $resolver = new AmazonS3Resolver($this->getAmazonS3Mock(), 'images.example.com');

        $this->assertAttributeInstanceOf('Psr\Log\NullLogger', 'logger', $resolver);
    }

    public function testNoDoubleSlashesInObjectUrlOnResolve()
    {
        $s3 = $this->getAmazonS3Mock();
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
        $s3 = $this->getAmazonS3Mock();
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

        $s3 = $this->getAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('create_object')
            ->will($this->returnValue($this->getCFResponseMock(false)))
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

        $s3 = $this->getAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('create_object')
            ->will($this->returnValue($this->getCFResponseMock(true)))
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $resolver->store($binary, 'foobar.jpg', 'thumb');
    }

    public function testIsStoredChecksObjectExistence()
    {
        $s3 = $this->getAmazonS3Mock();
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
        $s3 = $this->getAmazonS3Mock();
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

    public function testDeleteObjectIfObjectExistOnAmazonOnRemove()
    {
        $s3 = $this->getAmazonS3Mock();
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
            ->will($this->returnValue($this->getCFResponseMock(true)))
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $this->assertTrue($resolver->remove('some-folder/path.jpg', 'thumb'));
    }

    public function testDoNothingIfObjectNotExistOnAmazonOnRemove()
    {
        $s3 = $this->getAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('if_object_exists')
            ->with('images.example.com', 'thumb/some-folder/path.jpg')
            ->will($this->returnValue(false))
        ;
        $s3
            ->expects($this->never())
            ->method('delete_object')
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $this->assertTrue($resolver->remove('some-folder/path.jpg', 'thumb'));
    }

    public function testClearIsDisabled()
    {
        $s3 = $this->getAmazonS3Mock();
        $s3
            ->expects($this->never())
            ->method('delete_object')
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->clear('');
    }

    protected function getCFResponseMock($ok = true)
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
    protected function getAmazonS3Mock()
    {
        $mockedMethods = array(
            'if_object_exists',
            'create_object',
            'get_object_url',
            'delete_object',
            'authenticate'
        );

        return $this->getMock('AmazonS3', $mockedMethods, array(), '', false);
    }
}
