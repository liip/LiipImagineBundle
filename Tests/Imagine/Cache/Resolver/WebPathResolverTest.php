<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\HttpFoundation\Request;

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

        $this->config = $this->createFilterConfigurationMock();
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
            $this->config, $this->createRouterMock(), $this->webRoot, 'web_path'
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

        // guard
        $this->assertFalse($this->resolver->isStored($path, 'thumbnail'));

        // Store the cached version of that image.
        $content = file_get_contents($this->dataRoot.'/cats.jpeg');
        $binary = new Binary($content, 'image/jpeg', 'jpeg');
        $this->assertNull($this->resolver->store($binary, $path, 'thumbnail'));
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

        $this->assertEquals(
            '/media/cache/thumbnail/cats.jpeg',
            $this->resolver->resolve($path, 'thumbnail'),
            '->resolve() returns the expected Location of the cached image.'
        );
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

        $this->assertEquals(
            '/media/cache/thumbnail/cats.jpeg',
            $this->resolver->resolve($path, 'thumbnail'),
            '->resolve() returns the expected url of the cached image.'
        );
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

        // guard
        $this->assertFalse($this->resolver->isStored($path, 'thumbnail'));

        // Store the cached version of that image.
        $content = file_get_contents($this->dataRoot.'/cats.jpeg');
        $binary = new Binary($content, 'image/jpeg', 'jpeg');
        $this->resolver->store($binary, $path, 'thumbnail');

        $this->assertTrue(file_exists($filePath),
            '->store() creates the cached image file to be served.');
        $this->assertEquals($content, file_get_contents($filePath),
            '->store() writes the content of the original binary into the cache file.');
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

        // guard
        $this->assertFalse($this->resolver->isStored($path, 'thumbnail'));

        $this->filesystem->mkdir(dirname($filePath));
        file_put_contents($filePath, file_get_contents($this->dataRoot.'/cats.jpeg'));

        $this->assertEquals(
            '/sandbox/media/cache/thumbnail/cats.jpeg',
            $this->resolver->resolve($path, 'thumbnail'),
            '->resolve() returns the expected Location of the cached image.'
        );
    }

    public function testThrowIfRequestNotSetOnResolve()
    {
        $this->resolver->setRequest(null);

        $this->setExpectedException('LogicException', 'The request was not injected, inject it before using resolver.');
        $this->resolver->resolve('/a/path', 'aFilter');
    }

    public function testDoNothingIfFiltersAndPathsEmptyOnRemove()
    {
        $this->resolver->remove(array(), array());
    }

    public function testRemoveCacheForPathAndFilterOnRemove()
    {
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('generateUrl')
            ->will($this->returnValue('/media/cache/thumbnail/cats.jpeg'))
        ;

        $expectedFilePath = $this->createCache('cats.jpeg', 'thumbnail');

        $this->resolver->setRequest(Request::create('/'));

        $this->resolver->remove(array('cats.jpeg'), array('thumbnail'));

        $this->assertFileNotExists($expectedFilePath);
    }

    public function testRemoveCacheForSomePathsAndFilterOnRemove()
    {
        $this->cacheManager
            ->expects($this->at(0))
            ->method('generateUrl')
            ->will($this->returnValue('/media/cache/thumbnail/cats.jpeg'))
        ;
        $this->cacheManager
            ->expects($this->at(1))
            ->method('generateUrl')
            ->will($this->returnValue('/media/cache/thumbnail/cats.gif'))
        ;

        $expectedFilePathOne = $this->createCache('cats.jpeg', 'thumbnail');
        $expectedFilePathTwo = $this->createCache('dogs.jpeg', 'thumbnail');

        $this->resolver->setRequest(Request::create('/'));

        $this->resolver->remove(array('cats.jpeg', 'dogs.jpeg'), array('thumbnail'));

        $this->assertFileNotExists($expectedFilePathOne);
        $this->assertFileNotExists($expectedFilePathTwo);
    }

    public function testRemoveCacheForSomePathsAndSomeFiltersOnRemove()
    {
        $this->cacheManager
            ->expects($this->at(0))
            ->method('generateUrl')
            ->will($this->returnValue('/media/cache/filterOne/cats.jpeg'))
        ;
        $this->cacheManager
            ->expects($this->at(1))
            ->method('generateUrl')
            ->will($this->returnValue('/media/cache/filterOne/cats.gif'))
        ;
        $this->cacheManager
            ->expects($this->at(3))
            ->method('generateUrl')
            ->will($this->returnValue('/media/cache/filterTwo/cats.jpeg'))
        ;
        $this->cacheManager
            ->expects($this->at(4))
            ->method('generateUrl')
            ->will($this->returnValue('/media/cache/filterTwo/cats.gif'))
        ;

        $expectedFilePathOne = $this->createCache('cats.jpeg', 'filterOne');
        $expectedFilePathTwo = $this->createCache('dogs.jpeg', 'filterOne');
        $expectedFilePathThree = $this->createCache('cats.jpeg', 'filterTwo');
        $expectedFilePathFour = $this->createCache('dogs.jpeg', 'filterTwo');

        $this->resolver->setRequest(Request::create('/'));

        $this->resolver->remove(
            array('cats.jpeg', 'dogs.jpeg'),
            array('filterOne', 'filterTwo')
        );

        $this->assertFileNotExists($expectedFilePathOne);
        $this->assertFileNotExists($expectedFilePathTwo);
        $this->assertFileNotExists($expectedFilePathThree);
        $this->assertFileNotExists($expectedFilePathFour);
    }

    public function testDoNothingWhenFileNotExistForPathAndFilterOnRemove()
    {
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('generateUrl')
            ->will($this->returnValue('/media/cache/thumbnail/cats.jpeg'))
        ;

        $this->resolver->setRequest(Request::create('/'));

        $this->resolver->remove(array('cats.jpeg'), array('thumbnail'));
    }

    public function testRemoveCacheForFilterOnRemove()
    {
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('generateUrl')
            ->will($this->returnValue('/media/cache/filter/cats.jpeg'))
        ;

        $filePathOne = $this->createCache('cats.jpeg', 'filter');
        $filePathTwo = $this->createCache('dogs.jpeg', 'filter');

        $this->resolver->setRequest(Request::create('/'));

        $this->resolver->remove(array(), array('filter'));

        $this->assertFileNotExists($filePathOne);
        $this->assertFileNotExists($filePathTwo);
    }

    public function testRemoveCacheForSomeFiltersOnRemove()
    {
        $this->cacheManager
            ->expects($this->atLeastOnce())
            ->method('generateUrl')
            ->will($this->returnValue('/media/cache/filterOne/cats.jpeg'))
        ;

        $filePathOne = $this->createCache('cats.jpeg', 'filterOne');
        $filePathTwo = $this->createCache('dogs.jpeg', 'filterOne');
        $filePathThree = $this->createCache('cats.jpeg', 'filterTwo');
        $filePathFour = $this->createCache('dogs.jpeg', 'filterTwo');

        $this->resolver->setRequest(Request::create('/'));

        $this->resolver->remove(array(), array('filterOne', 'filterTwo'));

        $this->assertFileNotExists($filePathOne);
        $this->assertFileNotExists($filePathTwo);
        $this->assertFileNotExists($filePathThree);
        $this->assertFileNotExists($filePathFour);
    }

    protected function createCache($path, $filter, $fixtureFile = 'cats.jpeg')
    {
        $filePath = $this->webRoot."/media/cache/{$filter}/{$path}";

        $this->filesystem->mkdir(dirname($filePath));
        file_put_contents($filePath, file_get_contents($this->dataRoot.'/'.$fixtureFile));

        return $filePath;
    }
}
