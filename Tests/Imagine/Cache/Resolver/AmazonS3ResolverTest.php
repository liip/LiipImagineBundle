<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\Resolver\AmazonS3Resolver;
use Liip\ImagineBundle\Tests\AbstractTest;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\AmazonS3Resolver
 */
class AmazonS3ResolverTest extends AbstractTest
{
    protected function setUp()
    {
        parent::setUp();

        if (!class_exists('AmazonS3')) {
            require_once($this->fixturesDir.'/AmazonS3.php');
        }
    }

    public function testNoDoubleSlashesInObjectUrl()
    {
        $s3 = $this->getMock('AmazonS3');
        $s3
            ->expects($this->once())
            ->method('if_object_exists')
            ->with('images.example.com', 'thumb/some-folder/targetpath.jpg')
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

    public function testBrowserPathNotExisting()
    {
        $s3 = $this->getMock('AmazonS3');
        $s3
            ->expects($this->once())
            ->method('if_object_exists')
            ->with('images.example.com', 'thumb/some-folder/targetpath.jpg')
            ->will($this->returnValue(false))
        ;
        $s3
            ->expects($this->never())
            ->method('get_object_url')
        ;

        $cacheManager = $this->getMockCacheManager();
        $cacheManager
            ->expects($this->once())
            ->method('generateUrl')
            ->with('/some-folder/targetpath.jpg', 'thumb', false)
            ->will($this->returnValue('/media/cache/thumb/some-folder/targetpath.jpg'))
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->setCacheManager($cacheManager);

        $this->assertEquals('/media/cache/thumb/some-folder/targetpath.jpg', $resolver->getBrowserPath('/some-folder/targetpath.jpg', 'thumb'));
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

    public function testResolveNewObject()
    {
        $s3 = $this->getMock('AmazonS3');
        $s3
            ->expects($this->once())
            ->method('if_object_exists')
            ->will($this->returnValue(false))
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $targetPath = $resolver->resolve(new Request(), '/some-folder/targetpath.jpg', 'thumb');

        $this->assertEquals('thumb/some-folder/targetpath.jpg', $targetPath);
    }

    public function testResolveRedirectsOnExisting()
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
            ->with('images.example.com', 'thumb/some-folder/targetpath.jpg', 0, array())
            ->will($this->returnValue('http://images.example.com/some-folder/targetpath.jpg'))
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $response = $resolver->resolve(new Request(), '/some-folder/targetpath.jpg', 'thumb');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://images.example.com/some-folder/targetpath.jpg', $response->headers->get('Location'));
    }

    public function testRemove()
    {
        $s3 = $this->getMock('AmazonS3');
        $s3
            ->expects($this->once())
            ->method('if_object_exists')
            ->with('images.example.com', 'thumb/some-folder/targetpath.jpg')
            ->will($this->returnValue(true))
        ;
        $s3
            ->expects($this->once())
            ->method('delete_object')
            ->with('images.example.com', 'thumb/some-folder/targetpath.jpg')
            ->will($this->returnValue($this->getS3ResponseMock(true)))
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $this->assertTrue($resolver->remove('thumb/some-folder/targetpath.jpg', 'thumb'));
    }

    public function testRemoveNotExisting()
    {
        $s3 = $this->getMock('AmazonS3');
        $s3
            ->expects($this->once())
            ->method('if_object_exists')
            ->with('images.example.com', 'thumb/some-folder/targetpath.jpg')
            ->will($this->returnValue(false))
        ;
        $s3
            ->expects($this->never())
            ->method('delete_object')
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $this->assertTrue($resolver->remove('thumb/some-folder/targetpath.jpg', 'thumb'));
    }

    public function testClearIsDisabled()
    {
        $s3 = $this->getMock('AmazonS3');
        $s3
            ->expects($this->never())
            ->method('delete_object')
        ;

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->clear('');
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
