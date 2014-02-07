<?php


namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\Resolver\ProxyResolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;


/**
 * ProxyResolverTest
 *
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class ProxyResolverTest extends AbstractTest
{
    /**
     * @var ResolverInterface
     */
    private $primaryResolver;

    /**
     * @var ProxyResolver
     */
    private $resolver;

    public function setUp()
    {
        $this->primaryResolver = $this->getMock('Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface');

        $this->resolver = new ProxyResolver($this->primaryResolver, array('http://images.example.com'));
    }

    public function testResolveWithResponse()
    {
        $this->primaryResolver
            ->expects($this->once())
            ->method('resolve')
            ->will($this->returnValue(new RedirectResponse('app_dev.php/thumbs/foo/bar/bazz.png')));

        $result = $this->resolver->resolve(new Request(), '/foo/bar/bazz.png', 'test');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $result);

        if ('2' == Kernel::MAJOR_VERSION && '0' == Kernel::MINOR_VERSION) {
            $this->assertEquals('http://images.example.com/thumbs/foo/bar/bazz.png', $result->headers->get('Location'));
        } else {
            $this->assertEquals('http://images.example.com/thumbs/foo/bar/bazz.png', $result->getTargetUrl());
        }
    }

    public function testResolveWithoutResponse()
    {
        $this->primaryResolver
            ->expects($this->once())
            ->method('resolve')
            ->will($this->returnValue('app_dev.php/thumbs/foo/bar/bazz.png'));

        $result = $this->resolver->resolve(new Request(), '/foo/bar/bazz.png', 'test');

        $this->assertEquals('app_dev.php/thumbs/foo/bar/bazz.png', $result);
    }

    public function testStoreWithResponse()
    {
        $this->primaryResolver
            ->expects($this->once())
            ->method('store')
            ->will($this->returnValue(new RedirectResponse('http://foo.com/thumbs/foo/bar/bazz.png')));

        $result = $this->resolver->store(new Response(), '/foo/bar/bazz.png', 'test');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $result);

        if ('2' == Kernel::MAJOR_VERSION && '0' == Kernel::MINOR_VERSION) {
            $this->assertEquals('http://images.example.com/thumbs/foo/bar/bazz.png', $result->headers->get('Location'));
        } else {
            $this->assertEquals('http://images.example.com/thumbs/foo/bar/bazz.png', $result->getTargetUrl());
        }
    }

    public function testStoreWithoutResponse()
    {
        $this->primaryResolver
            ->expects($this->once())
            ->method('store')
            ->will($this->returnValue('http://foo.com/thumbs/foo/bar/bazz.png'));

        $result = $this->resolver->store(new Response(), '/foo/bar/bazz.png', 'test');

        $this->assertEquals('http://foo.com/thumbs/foo/bar/bazz.png', $result);
    }

    public function testGetBrowserPath()
    {
        $this->primaryResolver
            ->expects($this->once())
            ->method('getBrowserPath')
            ->will($this->returnValue('s3://myfunkybucket/thumbs/foo/bar/bazz.png'));

        $result = $this->resolver->getBrowserPath('/foo/bar/bazz.png', 'test');

        $this->assertEquals('http://images.example.com/thumbs/foo/bar/bazz.png', $result);
    }

    public function testRemove()
    {
        $this->primaryResolver
            ->expects($this->once())
            ->method('remove');

        $this->resolver->remove('/foo/bar/bazz.png', 'test');
    }

    public function testClear()
    {
        $this->primaryResolver
            ->expects($this->once())
            ->method('clear');

        $this->resolver->clear('test');
    }

} 