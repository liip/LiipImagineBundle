<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\Resolver\NoCacheResolver;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\AbstractFilesystemResolver
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\NoCacheResolver
 */
class NoCacheResolverTest extends \Phpunit_Framework_TestCase
{
    public function testThrowIfRequestNotSetOnResolve()
    {
        $resolver = new NoCacheResolver(new Filesystem);
        $resolver->setRequest(null);

        $this->setExpectedException('LogicException', 'The request was not injected, inject it before using resolver.');
        $resolver->resolve('/a/path', 'aFilter');
    }
}
