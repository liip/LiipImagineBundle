<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\Resolver\NoCacheWebPathResolver;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\AbstractFilesystemResolver
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\NoCacheWebPathResolver
 */
class NoCacheWebPathResolverTest extends AbstractTest
{
    public function testReturnAbsoluteUrlOfOriginalImageOnResolve()
    {
        $request = Request::create('http://foo.com');

        $resolver = new NoCacheWebPathResolver(new Filesystem);
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

        $resolver = new NoCacheWebPathResolver(new Filesystem);
        $resolver->setRequest($request);

        $this->assertEquals('http://foo.com/a/path', $resolver->resolve('a/path', 'aFilter'));
    }

    public function testDoNothingOnStore()
    {
        $resolver = new NoCacheWebPathResolver(new Filesystem);
        $resolver->setRequest(null);

        $this->assertNull($resolver->store(
            new Binary('aContent', 'image/jpeg', 'jpg'),
            'a/path',
            'aFilter')
        );
    }

    public function testDoNothingForPathAndFilterOnRemove()
    {
        $resolver = new NoCacheWebPathResolver(new Filesystem);
        $resolver->setRequest(null);

        $resolver->remove(array('a/path'), array('aFilter'));
    }

    public function testDoNothingForSomePathsAndSomeFiltersOnRemove()
    {
        $resolver = new NoCacheWebPathResolver(new Filesystem);
        $resolver->setRequest(null);

        $resolver->remove(array('foo', 'bar'), array('foo', 'bar'));
    }

    public function testDoNothingForEmptyPathAndEmptyFilterOnRemove()
    {
        $resolver = new NoCacheWebPathResolver(new Filesystem);
        $resolver->setRequest(null);

        $resolver->remove(array(), array());
    }
}
