<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\Resolver\NoCacheWebPathResolver;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\NoCacheWebPathResolver
 */
class NoCacheWebPathResolverTest extends AbstractTest
{
    public function testCouldBeConstructedWithRequestContextAsArgument()
    {
        new NoCacheWebPathResolver(new RequestContext);
    }

    public function testComposeSchemaHostAndPathOnResolve()
    {
        $context = new RequestContext('', 'GET', 'theHost', 'theSchema');

        $resolver = new NoCacheWebPathResolver($context);

        $this->assertEquals('theschema://theHost/aPath', $resolver->resolve('aPath', 'aFilter'));
    }

    public function testDoNothingOnStore()
    {
        $resolver = new NoCacheWebPathResolver(new RequestContext);

        $this->assertNull($resolver->store(
            new Binary('aContent', 'image/jpeg', 'jpg'),
            'a/path',
            'aFilter'
        ));
    }

    public function testDoNothingForPathAndFilterOnRemove()
    {
        $resolver = new NoCacheWebPathResolver(new RequestContext);

        $resolver->remove(array('a/path'), array('aFilter'));
    }

    public function testDoNothingForSomePathsAndSomeFiltersOnRemove()
    {
        $resolver = new NoCacheWebPathResolver(new RequestContext);

        $resolver->remove(array('foo', 'bar'), array('foo', 'bar'));
    }

    public function testDoNothingForEmptyPathAndEmptyFilterOnRemove()
    {
        $resolver = new NoCacheWebPathResolver(new RequestContext);

        $resolver->remove(array(), array());
    }
}
