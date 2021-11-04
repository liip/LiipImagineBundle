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

use Liip\ImagineBundle\Imagine\Cache\Resolver\NoCacheWebPathResolver;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\Routing\RequestContext;

/**
 * @covers \Liip\ImagineBundle\Imagine\Cache\Resolver\NoCacheWebPathResolver
 */
class NoCacheWebPathResolverTest extends AbstractTest
{
    public function testCouldBeConstructedWithRequestContextAsArgument(): void
    {
        $resolver = new NoCacheWebPathResolver(new RequestContext());

        $this->assertInstanceOf(NoCacheWebPathResolver::class, $resolver);
    }

    public function testComposeSchemaHostAndPathOnResolve(): void
    {
        $resolver = new NoCacheWebPathResolver(new RequestContext('', 'GET', 'thehost', 'theSchema'));

        $this->assertSame('theschema://thehost/aPath', $resolver->resolve('aPath', 'aFilter'));
    }

    public function testDoNothingOnStore(): void
    {
        $resolver = new NoCacheWebPathResolver(new RequestContext());

        $this->assertNull($resolver->store(new Binary('aContent', 'image/jpeg', 'jpg'), 'a/path', 'aFilter'));
    }

    public function testDoNothingForPathAndFilterOnRemove(): void
    {
        $resolver = new NoCacheWebPathResolver(new RequestContext());
        $resolver->remove(['a/path'], ['aFilter']);

        $this->assertInstanceOf(NoCacheWebPathResolver::class, $resolver);
    }

    public function testDoNothingForSomePathsAndSomeFiltersOnRemove(): void
    {
        $resolver = new NoCacheWebPathResolver(new RequestContext());
        $resolver->remove(['foo', 'bar'], ['foo', 'bar']);

        $this->assertInstanceOf(NoCacheWebPathResolver::class, $resolver);
    }

    public function testDoNothingForEmptyPathAndEmptyFilterOnRemove(): void
    {
        $resolver = new NoCacheWebPathResolver(new RequestContext());
        $resolver->remove([], []);

        $this->assertInstanceOf(NoCacheWebPathResolver::class, $resolver);
    }
}
