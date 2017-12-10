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
    public function testCouldBeConstructedWithRequestContextAsArgument()
    {
        $resolver = new NoCacheWebPathResolver(new RequestContext());

        $this->assertInstanceOf(NoCacheWebPathResolver::class, $resolver);
    }

    public function testComposeSchemaHostAndPathOnResolve()
    {
        $resolver = new NoCacheWebPathResolver(new RequestContext('', 'GET', 'thehost', 'theSchema'));

        $this->assertEquals('theschema://thehost/aPath', $resolver->resolve('aPath', 'aFilter'));
    }

    public function testDoNothingOnStore()
    {
        $resolver = new NoCacheWebPathResolver(new RequestContext());

        $this->assertNull($resolver->store(new Binary('aContent', 'image/jpeg', 'jpg'), 'a/path', 'aFilter'));
    }

    public function testDoNothingForPathAndFilterOnRemove()
    {
        $resolver = new NoCacheWebPathResolver(new RequestContext());
        $resolver->remove(array('a/path'), array('aFilter'));

        $this->assertInstanceOf(NoCacheWebPathResolver::class, $resolver);
    }

    public function testDoNothingForSomePathsAndSomeFiltersOnRemove()
    {
        $resolver = new NoCacheWebPathResolver(new RequestContext());
        $resolver->remove(array('foo', 'bar'), array('foo', 'bar'));

        $this->assertInstanceOf(NoCacheWebPathResolver::class, $resolver);
    }

    public function testDoNothingForEmptyPathAndEmptyFilterOnRemove()
    {
        $resolver = new NoCacheWebPathResolver(new RequestContext());
        $resolver->remove(array(), array());

        $this->assertInstanceOf(NoCacheWebPathResolver::class, $resolver);
    }
}
