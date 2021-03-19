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

use Liip\ImagineBundle\Imagine\Cache\Resolver\ProxyResolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Liip\ImagineBundle\Imagine\Cache\Resolver\ProxyResolver
 */
class ProxyResolverTest extends AbstractTest
{
    /**
     * @var MockObject|ResolverInterface
     */
    private $primaryResolver;

    /**
     * @var ProxyResolver
     */
    private $resolver;

    protected function setUp(): void
    {
        $this->primaryResolver = $this->createObjectMock(ResolverInterface::class);
        $this->resolver = new ProxyResolver($this->primaryResolver, ['http://images.example.com']);
    }

    public function testProxyCallAndRewriteReturnedUrlOnResolve(): void
    {
        $expectedPath = '/foo/bar/bazz.png';
        $expectedFilter = 'test';

        $this->primaryResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($expectedPath, $expectedFilter)
            ->willReturn('http://foo.com/thumbs/foo/bar/bazz.png');

        $result = $this->resolver->resolve($expectedPath, $expectedFilter);

        $this->assertSame('http://images.example.com/thumbs/foo/bar/bazz.png', $result);
    }

    public function testProxyCallAndRewriteReturnedUrlEvenSchemesDiffersOnResolve(): void
    {
        $expectedPath = '/foo/bar/bazz.png';
        $expectedFilter = 'test';

        $this->primaryResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($expectedPath, $expectedFilter)
            ->willReturn('http://foo.com/thumbs/foo/bar/bazz.png');

        $result = $this->resolver->resolve($expectedPath, $expectedFilter);

        $this->assertSame('http://images.example.com/thumbs/foo/bar/bazz.png', $result);
    }

    public function testProxyCallAndRewriteReturnedUrlWithMatchReplaceOnResolve(): void
    {
        $expectedPath = '/foo/bar/bazz.png';
        $expectedFilter = 'test';

        $this->primaryResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($expectedPath, $expectedFilter)
            ->willReturn('https://s3-eu-west-1.amazonaws.com/s3-cache.example.com/thumbs/foo/bar/bazz.png');

        $this->resolver = new ProxyResolver($this->primaryResolver, [
            'https://s3-eu-west-1.amazonaws.com/s3-cache.example.com' => 'http://images.example.com',
        ]);

        $result = $this->resolver->resolve($expectedPath, $expectedFilter);

        $this->assertSame('http://images.example.com/thumbs/foo/bar/bazz.png', $result);
    }

    public function testProxyCallAndRewriteReturnedUrlWithRegExpOnResolve(): void
    {
        $expectedPath = '/foo/bar/bazz.png';
        $expectedFilter = 'test';

        $this->primaryResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($expectedPath, $expectedFilter)
            ->willReturn('http://foo.com/thumbs/foo/bar/bazz.png');

        $this->resolver = new ProxyResolver($this->primaryResolver, [
            'regexp/http:\/\/.*?\//' => 'http://bar.com/',
        ]);

        $result = $this->resolver->resolve($expectedPath, $expectedFilter);

        $this->assertSame('http://bar.com/thumbs/foo/bar/bazz.png', $result);
    }

    public function testProxyCallAndReturnedValueOnIsStored(): void
    {
        $expectedPath = 'thePath';
        $expectedFilter = 'theFilter';

        $this->primaryResolver
            ->expects($this->once())
            ->method('isStored')
            ->with($expectedPath, $expectedFilter)
            ->willReturn(true);

        $this->assertTrue($this->resolver->isStored($expectedPath, $expectedFilter));
    }

    public function testProxyCallOnStore(): void
    {
        $expectedPath = 'thePath';
        $expectedFilter = 'theFilter';
        $expectedBinary = new Binary('aContent', 'image/png', 'png');

        $this->primaryResolver
            ->expects($this->once())
            ->method('store')
            ->with($expectedBinary, $expectedPath, $expectedFilter);

        $this->resolver->store($expectedBinary, $expectedPath, $expectedFilter);
    }

    public function testProxyCallOnRemove(): void
    {
        $expectedPaths = ['thePath'];
        $expectedFilters = ['theFilter'];

        $this->primaryResolver
            ->expects($this->once())
            ->method('remove')
            ->with($expectedPaths, $expectedFilters);

        $this->resolver->remove($expectedPaths, $expectedFilters);
    }
}
