<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\Resolver\NoCacheResolver;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\AbstractFilesystemResolver
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\NoCacheResolver
 */
class NoCacheResolverTest extends AbstractTest
{
    public function testReturnAbsoluteUrlOfOriginalImageOnResolve()
    {
        $request = Request::create('http://foo.com');

        $resolver = new NoCacheResolver(new Filesystem);
        $resolver->setRequest($request);

        $this->assertEquals('http://foo.com/a/path', $resolver->resolve('a/path', 'aFilter'));
    }

    public function testReturnAbsoluteUrlOfOriginalImageExcludingBaseUrlOnResolve()
    {
        $request = Request::create('http://foo.com');
        $request->server->set('SCRIPT_FILENAME', 'app.php');
        $request->server->set('SCRIPT_NAME', 'app.php');

        // guard
        $this->assertNotNull($request->getBaseUrl());

        $resolver = new NoCacheResolver(new Filesystem);
        $resolver->setRequest($request);

        $this->assertEquals('http://foo.com/a/path', $resolver->resolve('a/path', 'aFilter'));
    }

    public function testDoNothingOnStore()
    {
        $resolver = new NoCacheResolver(new Filesystem);
        $resolver->setRequest(null);

        $this->assertNull($resolver->store(new Response(), 'a/path', 'aFilter'));
    }
}
