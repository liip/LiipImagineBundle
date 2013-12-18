<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver
 */
class AwsS3ResolverTest extends AbstractTest
{
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
        $response = new Response();
        $response->setContent('foo');
        $response->headers->set('Content-Type', 'image/jpeg');

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('putObject')
            ->will($this->throwException(new \Exception))
        ;

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger
            ->expects($this->once())
            ->method('warning')
        ;

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->setLogger($logger);

        $this->assertSame($response, $resolver->store($response, 'foobar.jpg', 'thumb'));
    }

    public function testCreatedObjectRedirects()
    {
        $response = new Response();
        $response->setContent('foo');
        $response->headers->set('Content-Type', 'image/jpeg');

        $responseMock = $this->getS3ResponseMock();

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('putObject')
            ->will($this->returnValue($responseMock))
        ;

        $responseMock
            ->expects($this->once())
            ->method('get')
            ->with('ObjectURL')
            ->will($this->returnValue('http://images.example.com/thumb/foobar.jpg'))
        ;

        $resolver = new AwsS3Resolver($s3, 'images.example.com');

        $this->assertSame($response, $resolver->store($response, 'thumb/foobar.jpg', 'thumb'));
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://images.example.com/thumb/foobar.jpg', $response->headers->get('Location'));
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

    public function testDeleteObjectIfObjectExistOnAmazonOnRemove()
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
                'Key'    => 'thumb/some-folder/path.jpg',
            ))
            ->will($this->returnValue($this->getS3ResponseMock(true)))
        ;

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $this->assertTrue($resolver->remove('some-folder/path.jpg', 'thumb'));
    }

    public function testDoNothingIfObjectNotExistOnAmazonOnRemove()
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
        $this->assertTrue($resolver->remove('some-folder/path.jpg', 'thumb'));
    }

    public function testClearIsDisabled()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->never())
            ->method('deleteObject')
        ;

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->clear('');
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
            'createObject',
            'putObject',
            'doesObjectExist',
            'getObjectUrl',
        );

        return $this->getMock('Aws\S3\S3Client', $mockedMethods, array(), '', false);
    }

}
