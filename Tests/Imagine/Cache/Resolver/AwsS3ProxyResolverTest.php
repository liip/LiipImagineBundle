<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3ProxyResolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver;
use Liip\ImagineBundle\Tests\AbstractTest;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3ProxyResolver
 */
class AwsS3ProxyResolverTest extends AbstractTest
{
    protected function setUp()
    {
        parent::setUp();

        if (!class_exists('Aws\S3\S3Client')) {
            require_once($this->fixturesDir.'/S3Client.php');
        }

        if (!class_exists('Aws\S3\Enum\CannedAcl')) {
            require_once($this->fixturesDir.'/CannedAcl.php');
        }

        if (!class_exists('Guzzle\Service\Resource\Model')) {
            require_once($this->fixturesDir.'/Model.php');
        }
    }

    public function testBrowserPathWithProxy()
    {
        $s3 = $this->getMock('Aws\S3\S3Client');
        $s3
            ->expects($this->once())
            ->method('doesObjectExist')
            ->with('images.example.com', 'thumb/some-folder/targetpath.jpg')
            ->will($this->returnValue(true))
        ;
        $s3
            ->expects($this->never())
            ->method('getObjectUrl')
        ;

        $resolver = new AwsS3ProxyResolver($s3, 'images.example.com');
        $resolver->setProxyHost('http://images.website.com');
        $result = $resolver->getBrowserPath('/some-folder/targetpath.jpg', 'thumb');

        $this->assertEquals('http://images.website.com/thumb/some-folder/targetpath.jpg', $result);
    }

    public function testCreatedObjectRedirectsToProxy()
    {
        $response = new Response();
        $response->setContent('foo');
        $response->headers->set('Content-Type', 'image/jpeg');

        $responseMock = $this->getS3ResponseMock();

        $s3 = $this->getMock('Aws\S3\S3Client');
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

        $resolver = new AwsS3ProxyResolver($s3, 'images.example.com');
        $resolver->setProxyHost('http://images.website.com');

        $this->assertSame($response, $resolver->store($response, 'thumb/foobar.jpg', 'thumb'));
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://images.website.com/thumb/foobar.jpg', $response->headers->get('Location'));
    }

    public function testResolveRedirectsToProxy()
    {
        $response = new RedirectResponse('http://images.website.com/thumb/foobar.jpg', 301);

        $s3 = $this->getMock('Aws\S3\S3Client');
        $s3
            ->expects($this->once())
            ->method('doesObjectExist')
            ->with('images.example.com', 'thumb/foobar.jpg')
            ->will($this->returnValue(true))
        ;
        $s3
            ->expects($this->never())
            ->method('getObjectUrl')
        ;

        $resolver = new AwsS3ProxyResolver($s3, 'images.example.com');
        $resolver->setProxyHost('http://images.website.com');

        $this->assertEquals($response, $resolver->resolve(new Request(),'foobar.jpg', 'thumb'));
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://images.website.com/thumb/foobar.jpg', $response->headers->get('Location'));
    }

    protected function getS3ResponseMock($ok = true)
    {
        $s3Response = $this->getMock('Guzzle\Service\Resource\Model');

        return $s3Response;
    }

}
