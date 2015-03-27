<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\Resolver\ProxyResolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Model\Binary;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\ProxyResolver
 */
class ProxyResolverTest extends \Phpunit_Framework_TestCase
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

    public function testProxyCallAndRewriteReturnedUrlOnResolve()
    {
        $expectedPath = '/foo/bar/bazz.png';
        $expectedFilter = 'test';

        $this->primaryResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($expectedPath, $expectedFilter)
            ->will($this->returnValue('http://foo.com/thumbs/foo/bar/bazz.png'))
        ;

        $result = $this->resolver->resolve($expectedPath, $expectedFilter);

        $this->assertEquals('http://images.example.com/thumbs/foo/bar/bazz.png', $result);
    }

    public function testProxyCallAndRewriteReturnedUrlEvenSchemesDiffersOnResolve()
    {
        $expectedPath = '/foo/bar/bazz.png';
        $expectedFilter = 'test';

        $this->primaryResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($expectedPath, $expectedFilter)
            ->will($this->returnValue('http://foo.com/thumbs/foo/bar/bazz.png'))
        ;

        $result = $this->resolver->resolve($expectedPath, $expectedFilter);

        $this->assertEquals('http://images.example.com/thumbs/foo/bar/bazz.png', $result);
    }

    public function testProxyCallAndReturnedValueOnIsStored()
    {
        $expectedPath = 'thePath';
        $expectedFilter = 'theFilter';

        $this->primaryResolver
            ->expects($this->once())
            ->method('isStored')
            ->with($expectedPath, $expectedFilter)
            ->will($this->returnValue(true))
        ;

        $this->assertTrue($this->resolver->isStored($expectedPath, $expectedFilter));
    }

    public function testProxyCallOnStore()
    {
        $expectedPath = 'thePath';
        $expectedFilter = 'theFilter';
        $expectedBinary = new Binary('aContent', 'image/png', 'png');

        $this->primaryResolver
            ->expects($this->once())
            ->method('store')
            ->with($expectedBinary, $expectedPath, $expectedFilter)
        ;

        $this->resolver->store($expectedBinary, $expectedPath, $expectedFilter);
    }

    public function testProxyCallOnRemove()
    {
        $expectedPaths = array('thePath');
        $expectedFilters = array('theFilter');

        $this->primaryResolver
            ->expects($this->once())
            ->method('remove')
            ->with($expectedPaths, $expectedFilters)
        ;

        $this->resolver->remove($expectedPaths, $expectedFilters);
    }
}
