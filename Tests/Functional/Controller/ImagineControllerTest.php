<?php

namespace Liip\ImagineBundle\Tests\Functional\Controller;

use Liip\ImagineBundle\Tests\Functional\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\Filesystem\Filesystem;
use Liip\ImagineBundle\Imagine\Cache\Signer;

/**
 * @covers Liip\ImagineBundle\Controller\ImagineController
 */
class ImagineControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    protected $webRoot;

    protected $cacheRoot;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function setUp()
    {
        parent::setUp();

        $this->client = $this->createClient();

        $this->webRoot = self::$kernel->getContainer()->getParameter('kernel.root_dir').'/web';
        $this->cacheRoot = $this->webRoot.'/media/cache';

        $this->filesystem = new Filesystem();
        $this->filesystem->remove($this->cacheRoot);
    }

    public function testCouldBeGetFromContainer()
    {
        $controller = self::$kernel->getContainer()->get('liip_imagine.controller');

        $this->assertInstanceOf('Liip\ImagineBundle\Controller\ImagineController', $controller);
    }

    public function testShouldResolvePopulatingCacheFirst()
    {
        //guard
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');

        $this->client->request('GET', '/media/cache/resolve/thumbnail_web_path/images/cats.jpeg');

        $response = $this->client->getResponse();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://localhost/media/cache/thumbnail_web_path/images/cats.jpeg', $response->getTargetUrl());

        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
    }

    public function testShouldResolveFromCache()
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg',
            'anImageContent'
        );

        $this->client->request('GET', '/media/cache/resolve/thumbnail_web_path/images/cats.jpeg');

        $response = $this->client->getResponse();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://localhost/media/cache/thumbnail_web_path/images/cats.jpeg', $response->getTargetUrl());

        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @expectedExceptionMessage Signed url does not pass the sign check for path "images/cats.jpeg" and filter "thumbnail_web_path" and runtime config {"thumbnail":{"size":["50","50"]}}
     */
    public function testThrowBadRequestIfSignInvalidWhileUsingCustomFilters()
    {
        $this->client->request('GET', '/media/cache/resolve/thumbnail_web_path/rc/invalidHash/images/cats.jpeg?'.http_build_query(array(
            'filters' => array(
                'thumbnail' => array('size' => array(50, 50)),
            ),
            '_hash' => 'invalid',
        )));
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Filters must be an array. Value was "some-string"
     */
    public function testShouldThrowNotFoundHttpExceptionIfFiltersNotArray()
    {
        $this->client->request('GET', '/media/cache/resolve/thumbnail_web_path/rc/invalidHash/images/cats.jpeg?'.http_build_query(array(
            'filters' => 'some-string',
            '_hash' => 'hash',
        )));
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Source image could not be found
     */
    public function testShouldThrowNotFoundHttpExceptionIfFileNotExists()
    {
        $this->client->request('GET', '/media/cache/resolve/thumbnail_web_path/images/shrodinger_cats_which_not_exist.jpeg');
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testInvalidFilterShouldThrowNotFoundHttpException()
    {
        $this->client->request('GET', '/media/cache/resolve/invalid-filter/images/cats.jpeg');
    }

    public function testShouldResolveWithCustomFiltersPopulatingCacheFirst()
    {
        /** @var Signer $signer */
        $signer = self::$kernel->getContainer()->get('liip_imagine.cache.signer');

        $params = array(
            'filters' => array(
                'thumbnail' => array('size' => array(50, 50)),
            ),
        );

        $path = 'images/cats.jpeg';

        $hash = $signer->sign($path, $params['filters']);

        $expectedCachePath = 'thumbnail_web_path/rc/'.$hash.'/'.$path;

        $url = 'http://localhost/media/cache/resolve/'.$expectedCachePath.'?'.http_build_query($params);

        //guard
        $this->assertFileNotExists($this->cacheRoot.'/'.$expectedCachePath);

        $this->client->request('GET', $url);

        $response = $this->client->getResponse();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://localhost/media/cache/'.$expectedCachePath, $response->getTargetUrl());

        $this->assertFileExists($this->cacheRoot.'/'.$expectedCachePath);
    }

    public function testShouldResolveWithCustomFiltersFromCache()
    {
        /** @var Signer $signer */
        $signer = self::$kernel->getContainer()->get('liip_imagine.cache.signer');

        $params = array(
            'filters' => array(
                'thumbnail' => array('size' => array(50, 50)),
            ),
        );

        $path = 'images/cats.jpeg';

        $hash = $signer->sign($path, $params['filters']);

        $expectedCachePath = 'thumbnail_web_path/rc/'.$hash.'/'.$path;

        $url = 'http://localhost/media/cache/resolve/'.$expectedCachePath.'?'.http_build_query($params);

        $this->filesystem->dumpFile(
            $this->cacheRoot.'/'.$expectedCachePath,
            'anImageContent'
        );

        $this->client->request('GET', $url);

        $response = $this->client->getResponse();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://localhost/media/cache'.'/'.$expectedCachePath, $response->getTargetUrl());

        $this->assertFileExists($this->cacheRoot.'/'.$expectedCachePath);
    }

    public function testShouldResolvePathWithSpecialCharactersAndWhiteSpaces()
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/foo bar.jpeg',
            'anImageContent'
        );

        // we are calling url with encoded file name as it will be called by browser
        $urlEncodedFileName = 'foo+bar';
        $this->client->request('GET', '/media/cache/resolve/thumbnail_web_path/images/'.$urlEncodedFileName.'.jpeg');

        $response = $this->client->getResponse();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://localhost/media/cache/thumbnail_web_path/images/foo bar.jpeg', $response->getTargetUrl());

        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/foo bar.jpeg');
    }
}
