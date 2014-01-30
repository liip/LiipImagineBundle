<?php
namespace Liip\ImagineBundle\Tests\Functional\Controller;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Tests\Functional\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\UriSigner;

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

        $this->webRoot = self::$kernel->getContainer()->getParameter('liip_imagine.web_root');
        $this->cacheRoot = $this->webRoot.'/'.self::$kernel->getContainer()->getParameter('liip_imagine.cache_prefix');

        $this->filesystem = new Filesystem;
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

        $this->client->request('GET', '/media/cache/thumbnail_web_path/images/cats.jpeg');

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

        $this->client->request('GET', '/media/cache/thumbnail_web_path/images/cats.jpeg');

        $response = $this->client->getResponse();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://localhost/media/cache/thumbnail_web_path/images/cats.jpeg', $response->getTargetUrl());

        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @expectedExceptionMessage Signed url does not pass the sign check. Maybe it was modified by someone.
     */
    public function testThrowBadRequestIfSignInvalidWhileUsingCustomFilters()
    {
        $this->client->request('GET', '/media/cache/thumbnail_web_path/images/cats.jpeg?'.http_build_query(array(
            'filters' => array(
                'thumbnail' => array('size' => array(50, 50))
            ),
            '_hash' => 'invalid',
        )));
    }

    public function testShouldResolveWithCustomFiltersPopulatingCacheFirst()
    {
        /** @var UriSigner $uriSigner */
        $uriSigner = self::$kernel->getContainer()->get('liip_imagine.uri_signer');

        $url = 'http://localhost/media/cache/thumbnail_web_path/images/cats.jpeg?'.http_build_query(array(
            'filters' => array(
                'thumbnail' => array('size' => array(50, 50))
            ),
        ));
        $url = $uriSigner->sign($url);

        //guard
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');

        $this->client->request('GET', $url);

        $response = $this->client->getResponse();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://localhost/media/cache/thumbnail_web_path/S8rrlhhQ/images/cats.jpeg', $response->getTargetUrl());

        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/S8rrlhhQ/images/cats.jpeg');
    }

    public function testShouldResolveWithCustomFiltersFromCache()
    {
        $expectedCachePath = 'thumbnail_web_path/S8rrlhhQ/images/cats.jpeg';

        $this->filesystem->dumpFile(
            $this->cacheRoot.$expectedCachePath,
            'anImageContent'
        );

        /** @var UriSigner $uriSigner */
        $uriSigner = self::$kernel->getContainer()->get('liip_imagine.uri_signer');

        $url = 'http://localhost/media/cache/thumbnail_web_path/images/cats.jpeg?'.http_build_query(array(
            'filters' => array(
                'thumbnail' => array('size' => array(50, 50))
            ),
        ));
        $url = $uriSigner->sign($url);

        $this->client->request('GET', $url);

        $response = $this->client->getResponse();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://localhost/media/cache'.'/'.$expectedCachePath, $response->getTargetUrl());

        $this->assertFileExists($this->cacheRoot.'/'.$expectedCachePath);
    }
}
