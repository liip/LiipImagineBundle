<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\AbstractFilesystemResolver
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver
 */
class WebPathResolverTest extends AbstractTest
{
    protected $config;
    protected $cacheManager;

    /**
     * @var WebPathResolver
     */
    protected $resolver;

    protected $webRoot;
    protected $dataRoot;
    protected $cacheDir;

    protected function setUp()
    {
        parent::setUp();

        $this->config = $this->getMockFilterConfiguration();
        $this->config
            ->expects($this->any())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'cache' => null,
            )))
        ;

        $this->webRoot = $this->tempDir.'/root/web';
        $this->dataRoot = $this->fixturesDir.'/assets';
        $this->cacheDir = $this->webRoot.'/media/cache';

        $this->filesystem->mkdir($this->cacheDir);

        $this->cacheManager = $this->getMock('Liip\ImagineBundle\Imagine\Cache\CacheManager', array(
            'generateUrl',
        ), array(
            $this->config, $this->getMockRouter(), $this->webRoot, 'web_path'
        ));

        $this->resolver = new WebPathResolver($this->filesystem);
        $this->cacheManager->addResolver('web_path', $this->resolver);
    }

    public function testDefaultBehavior()
    {
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('generateUrl')
            ->will($this->returnValue(str_replace('/', DIRECTORY_SEPARATOR, '/media/cache/thumbnail/cats.jpeg')))
        ;

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request
            ->expects($this->atLeastOnce())
            ->method('getBaseUrl')
            ->will($this->returnValue('/app.php'))
        ;

        $this->resolver->setRequest($request);

        $path = 'cats.jpeg';

        // Resolve the requested image for the given filter.
        $this->assertNull($this->resolver->resolve($path, 'thumbnail'));

        // Store the cached version of that image.
        $content = file_get_contents($this->dataRoot.'/cats.jpeg');
        $response = new Response($content);
        $this->resolver->store($response, $path, 'thumbnail');
        $this->assertEquals(201, $response->getStatusCode(),
            '->store() alters the HTTP response code to "201 - Created".');
        $this->assertEquals($content, $response->getContent());
    }

    /**
     * @depends testDefaultBehavior
     */
    public function testMissingRewrite()
    {
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('generateUrl')
            ->will($this->returnValue('/media/cache/thumbnail/cats.jpeg'))
        ;

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request
            ->expects($this->atLeastOnce())
            ->method('getBaseUrl')
            ->will($this->returnValue(''))
        ;

        $this->resolver->setRequest($request);

        $path = 'cats.jpeg';
        $webFilePath = $this->webRoot.'/media/cache/thumbnail/cats.jpeg';

        // The file has already been cached by this resolver.
        $this->filesystem->mkdir(dirname($webFilePath));
        file_put_contents($webFilePath, file_get_contents($this->dataRoot.'/cats.jpeg'));

        $response = $this->resolver->resolve($path, 'thumbnail');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response,
            '->resolve() returns a Response instance if the target file already exists.');
        $this->assertEquals(302, $response->getStatusCode(),
            '->resolve() returns the HTTP response code "302 - Found".');
        $this->assertEquals('/media/cache/thumbnail/cats.jpeg', $response->headers->get('Location'),
            '->resolve() returns the expected Location of the cached image.');
    }

    /**
     * @depends testMissingRewrite
     */
    public function testMissingRewriteWithBaseUrl()
    {
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('generateUrl')
            ->will($this->returnValue('/app_dev.php/media/cache/thumbnail/cats.jpeg'))
        ;

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request
            ->expects($this->atLeastOnce())
            ->method('getBaseUrl')
            ->will($this->returnValue('/app_dev.php'))
        ;

        $this->resolver->setRequest($request);

        $path = 'cats.jpeg';
        $filePath = $this->webRoot.'/media/cache/thumbnail/cats.jpeg';

        // The file has already been cached by this resolver.
        $this->filesystem->mkdir(dirname($filePath));
        file_put_contents($filePath, file_get_contents($this->dataRoot.'/cats.jpeg'));

        $response = $this->resolver->resolve($path, 'thumbnail');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response,
            '->resolve() returns a Response instance if the target file already exists.');
        $this->assertEquals(302, $response->getStatusCode(),
            '->resolve() returns the HTTP response code "302 - Found".');
        $this->assertEquals('/media/cache/thumbnail/cats.jpeg', $response->headers->get('Location'),
            '->resolve() returns the expected Location of the cached image.');
    }

    /**
     * @depends testDefaultBehavior
     */
    public function testResolveWithBasePath()
    {
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('generateUrl')
            ->will($this->returnValue(str_replace('/', DIRECTORY_SEPARATOR, '/sandbox/app_dev.php/media/cache/thumbnail/cats.jpeg')))
        ;

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request
            ->expects($this->atLeastOnce())
            ->method('getBaseUrl')
            ->will($this->returnValue(str_replace('/', DIRECTORY_SEPARATOR, '/sandbox/app_dev.php')))
        ;

        $this->resolver->setRequest($request);

        $path = 'cats.jpeg';
        $filePath = $this->webRoot.'/media/cache/thumbnail/cats.jpeg';

        // Resolve the requested image for the given filter.
        $this->assertNull($this->resolver->resolve($path, 'thumbnail'));

        // Store the cached version of that image.
        $content = file_get_contents($this->dataRoot.'/cats.jpeg');
        $response = new Response($content);
        $this->resolver->store($response, $path, 'thumbnail');
        $this->assertEquals(201, $response->getStatusCode(),
            '->store() alters the HTTP response code to "201 - Created".');
        $this->assertTrue(file_exists($filePath),
            '->store() creates the cached image file to be served.');
        $this->assertEquals($content, file_get_contents($filePath),
            '->store() writes the content of the original Response into the cache file.');
    }

    /**
     * @depends testMissingRewrite
     * @depends testResolveWithBasePath
     */
    public function testMissingRewriteWithBasePathWithScriptname()
    {
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('generateUrl')
            ->will($this->returnValue('/sandbox/app_dev.php/media/cache/thumbnail/cats.jpeg'))
        ;

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request
            ->expects($this->atLeastOnce())
            ->method('getBasePath')
            ->will($this->returnValue('/sandbox'))
        ;
        $request
            ->expects($this->atLeastOnce())
            ->method('getBaseUrl')
            ->will($this->returnValue('/sandbox/app_dev.php'))
        ;

        $this->resolver->setRequest($request);

        $path = 'cats.jpeg';
        $filePath = $this->webRoot.'/media/cache/thumbnail/cats.jpeg';

        // Resolve the requested image for the given filter.
        $this->assertNull($this->resolver->resolve($path, 'thumbnail'));

        $this->filesystem->mkdir(dirname($filePath));
        file_put_contents($filePath, file_get_contents($this->dataRoot.'/cats.jpeg'));

        $response = $this->resolver->resolve($path, 'thumbnail');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response,
            '->resolve() returns a Response instance if the target file already exists.');
        $this->assertEquals(302, $response->getStatusCode(),
            '->resolve() returns the HTTP response code "302 - Found".');
        $this->assertEquals('/sandbox/media/cache/thumbnail/cats.jpeg', $response->headers->get('Location'),
            '->resolve() returns the expected Location of the cached image.');
    }

    public function testClear()
    {
        $filename = $this->cacheDir.'/thumbnails/cats.jpeg';
        $this->filesystem->mkdir(dirname($filename));
        file_put_contents($filename, '42');
        $this->assertTrue(file_exists($filename));

        $this->resolver->clear('/media/cache');

        $this->assertFalse(file_exists($filename));
    }

    public function testClearWithoutPrefix()
    {
        $filename = $this->cacheDir.'/thumbnails/cats.jpeg';
        $this->filesystem->mkdir(dirname($filename));
        file_put_contents($filename, '42');
        $this->assertTrue(file_exists($filename));

        try {
            // This would effectively clear the web root.
            $this->resolver->clear('');

            $this->fail('Clear should not work without a valid cache prefix');
        } catch (\Exception $e) { }

        $this->assertTrue(file_exists($filename));
    }

    public function testThrowIfRequestNotSetOnResolve()
    {
        $this->resolver->setRequest(null);

        $this->setExpectedException('LogicException', 'The request was not injected, inject it before using resolver.');
        $this->resolver->resolve('/a/path', 'aFilter');
    }

    public function testRemoveCachedImageWhenExistOnRemove()
    {
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('generateUrl')
            ->will($this->returnValue('/media/cache/thumbnail/cats.jpeg'))
        ;

        $path = 'cats.jpeg';
        $filePath = $this->webRoot.'/media/cache/thumbnail/cats.jpeg';

        $this->filesystem->mkdir(dirname($filePath));
        file_put_contents($filePath, file_get_contents($this->dataRoot.'/cats.jpeg'));

        $this->resolver->setRequest(Request::create('/'));

        // guard
        $this->assertNotNull($this->resolver->resolve($path, 'thumbnail'));

        $this->assertTrue($this->resolver->remove($path, 'thumbnail'));
        $this->assertFalse(file_exists($filePath));
    }

    public function testDoNothingIfCachedImageNotExistOnRemove()
    {
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('generateUrl')
            ->will($this->returnValue('/media/cache/thumbnail/cats.jpeg'))
        ;

        $path = 'cats.jpeg';
        $filePath = $this->webRoot.'/media/cache/thumbnail/cats.jpeg';

        // guard
        $this->assertFalse(file_exists($filePath));

        $this->resolver->setRequest(Request::create('/'));

        $this->assertTrue($this->resolver->remove($path, 'thumbnail'));
        $this->assertFalse(file_exists($filePath));
    }
}
