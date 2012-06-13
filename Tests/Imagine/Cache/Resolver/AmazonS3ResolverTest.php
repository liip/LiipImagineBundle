<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\Resolver\AmazonS3Resolver;

use Symfony\Component\HttpFoundation\Response;

class AmazonS3ResolverTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('AmazonS3')) {
            require_once(__DIR__.'/../../../Fixtures/AmazonS3.php');
        }
    }

    public function testNoDoubleSlashesInObjectUrl()
    {
        $s3 = $this->getMock('AmazonS3');
        $s3
            ->expects($this->once())
            ->method('if_object_exists')
            ->will($this->returnValue(true))
        ;
        $s3
            ->expects($this->once())
            ->method('get_object_url')
            ->with('images.example.com', 'thumb/some-folder/targetpath.jpg')
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->getBrowserPath('/some-folder/targetpath.jpg', 'thumb');
    }

    public function testObjUrlOptions()
    {
        $s3 = $this->getMock('AmazonS3');
        $s3
            ->expects($this->once())
            ->method('if_object_exists')
            ->will($this->returnValue(true))
        ;
        $s3
            ->expects($this->once())
            ->method('get_object_url')
            ->with('images.example.com', 'thumb/some-folder/targetpath.jpg', 0, array('torrent' => true))
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->setObjectUrlOption('torrent', true);
        $resolver->getBrowserPath('/some-folder/targetpath.jpg', 'thumb');
    }

    public function testLogNotCreatedObjects()
    {
        $response = new Response();
        $response->setContent('foo');
        $response->headers->set('Content-Type', 'image/jpeg');

        $s3 = $this->getMock('AmazonS3');
        $s3
            ->expects($this->once())
            ->method('create_object')
            ->will($this->returnValue($this->getS3ResponseMock(false)))
        ;

        $logger = $this->getMockForAbstractClass('Symfony\Component\HttpKernel\Log\LoggerInterface');
        $logger
            ->expects($this->once())
            ->method('warn')
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->setLogger($logger);

        $this->assertSame($response, $resolver->store($response, 'foobar.jpg', 'thumb'));
    }

    public function testCreatedObjectRedirects()
    {
        $response = new Response();
        $response->setContent('foo');
        $response->headers->set('Content-Type', 'image/jpeg');

        $s3 = $this->getMock('AmazonS3');
        $s3
            ->expects($this->once())
            ->method('create_object')
            ->will($this->returnValue($this->getS3ResponseMock(true)))
        ;
        $s3
            ->expects($this->once())
            ->method('get_object_url')
            ->with('images.example.com', 'thumb/foobar.jpg', 0, array())
            ->will($this->returnValue('http://images.example.com/thumb/foobar.jpg'))
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $this->assertSame($response, $resolver->store($response, 'thumb/foobar.jpg', 'thumb'));
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://images.example.com/thumb/foobar.jpg', $response->headers->get('Location'));
    }

    protected function getS3ResponseMock($ok = true)
    {
        $s3Response = $this->getMock('AmazonS3Response');
        $s3Response
            ->expects($this->once())
            ->method('isOK')
            ->will($this->returnValue($ok))
        ;

        return $s3Response;
    }
}
