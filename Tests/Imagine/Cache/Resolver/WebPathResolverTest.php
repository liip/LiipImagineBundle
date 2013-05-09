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

        // Resolve the requested image for the given filter.
        $targetPath = $this->resolver->resolve($request, 'cats.jpeg', 'thumbnail');
        // The realpath() is important for filesystems that are virtual in some way (encrypted, different mount options, ..)
        $this->assertEquals(str_replace('/', DIRECTORY_SEPARATOR, realpath($this->cacheDir).'/thumbnail/cats.jpeg'), $targetPath,
            '->resolve() correctly converts the requested file into target path within webRoot.');
        $this->assertFalse(file_exists($targetPath),
            '->resolve() does not create the file within the target path.');

        // Store the cached version of that image.
        $content = file_get_contents($this->dataRoot.'/cats.jpeg');
        $response = new Response($content);
        $this->resolver->store($response, $targetPath, 'thumbnail');
        $this->assertEquals(201, $response->getStatusCode(),
            '->store() alters the HTTP response code to "201 - Created".');
        $this->assertTrue(file_exists($targetPath),
            '->store() creates the cached image file to be served.');
        $this->assertEquals($content, file_get_contents($targetPath),
            '->store() writes the content of the original Response into the cache file.');

        // Remove the cached image.
        $this->assertTrue($this->resolver->remove($targetPath, 'thumbnail'),
            '->remove() reports removal of cached image file correctly.');
        $this->assertFalse(file_exists($targetPath),
            '->remove() actually removes the cached file from the filesystem.');
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

        // The file has already been cached by this resolver.
        $targetPath = $this->resolver->resolve($request, 'cats.jpeg', 'thumbnail');
        $this->filesystem->mkdir(dirname($targetPath));
        file_put_contents($targetPath, file_get_contents($this->dataRoot.'/cats.jpeg'));

        $response = $this->resolver->resolve($request, 'cats.jpeg', 'thumbnail');
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

        // The file has already been cached by this resolver.
        $targetPath = $this->resolver->resolve($request, 'cats.jpeg', 'thumbnail');
        $this->filesystem->mkdir(dirname($targetPath));
        file_put_contents($targetPath, file_get_contents($this->dataRoot.'/cats.jpeg'));

        $response = $this->resolver->resolve($request, 'cats.jpeg', 'thumbnail');
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

        // Resolve the requested image for the given filter.
        $targetPath = $this->resolver->resolve($request, 'cats.jpeg', 'thumbnail');
        // The realpath() is important for filesystems that are virtual in some way (encrypted, different mount options, ..)
        $this->assertEquals(str_replace('/', DIRECTORY_SEPARATOR, realpath($this->cacheDir).'/thumbnail/cats.jpeg'), $targetPath,
            '->resolve() correctly converts the requested file into target path within webRoot.');
        $this->assertFalse(file_exists($targetPath),
            '->resolve() does not create the file within the target path.');

        // Store the cached version of that image.
        $content = file_get_contents($this->dataRoot.'/cats.jpeg');
        $response = new Response($content);
        $this->resolver->store($response, $targetPath, 'thumbnail');
        $this->assertEquals(201, $response->getStatusCode(),
            '->store() alters the HTTP response code to "201 - Created".');
        $this->assertTrue(file_exists($targetPath),
            '->store() creates the cached image file to be served.');
        $this->assertEquals($content, file_get_contents($targetPath),
            '->store() writes the content of the original Response into the cache file.');

        // Remove the cached image.
        $this->assertTrue($this->resolver->remove($targetPath, 'thumbnail'),
            '->remove() reports removal of cached image file correctly.');
        $this->assertFalse(file_exists($targetPath),
            '->remove() actually removes the cached file from the filesystem.');
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

        // The file has already been cached by this resolver.
        $targetPath = $this->resolver->resolve($request, 'cats.jpeg', 'thumbnail');
        $this->filesystem->mkdir(dirname($targetPath));
        file_put_contents($targetPath, file_get_contents($this->dataRoot.'/cats.jpeg'));

        $response = $this->resolver->resolve($request, 'cats.jpeg', 'thumbnail');
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
}
