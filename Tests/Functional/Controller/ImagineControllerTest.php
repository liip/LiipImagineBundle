<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Functional\Controller;

use Liip\ImagineBundle\Controller\ImagineController;
use Liip\ImagineBundle\Imagine\Cache\Signer;
use Liip\ImagineBundle\Tests\Functional\AbstractSetupWebTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @covers \Liip\ImagineBundle\Controller\ImagineController
 */
class ImagineControllerTest extends AbstractSetupWebTestCase
{
    public function testCouldBeGetFromContainer()
    {
        $this->assertInstanceOf(ImagineController::class, self::$kernel->getContainer()->get(ImagineController::class));
    }

    public function testShouldResolvePopulatingCacheFirst()
    {
        //guard
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');

        self::$client->request('GET', '/media/cache/resolve/thumbnail_web_path/images/cats.jpeg');

        $response = self::$client->getResponse();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('http://localhost/media/cache/thumbnail_web_path/images/cats.jpeg', $response->getTargetUrl());

        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
    }

    public function testShouldResolveFromCache()
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg',
            'anImageContent'
        );

        self::$client->request('GET', '/media/cache/resolve/thumbnail_web_path/images/cats.jpeg');

        $response = self::$client->getResponse();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('http://localhost/media/cache/thumbnail_web_path/images/cats.jpeg', $response->getTargetUrl());

        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
    }

    public function testThrowBadRequestIfSignInvalidWhileUsingCustomFilters()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\BadRequestHttpException::class);
        $this->expectExceptionMessage('Signed url does not pass the sign check for path "images/cats.jpeg" and filter "thumbnail_web_path" and runtime config {"thumbnail":{"size":["50","50"]}}');

        self::$client->request('GET', '/media/cache/resolve/thumbnail_web_path/rc/invalidHash/images/cats.jpeg?'.http_build_query([
            'filters' => [
                'thumbnail' => ['size' => [50, 50]],
            ],
            '_hash' => 'invalid',
        ]));
    }

    public function testShouldThrowNotFoundHttpExceptionIfFiltersNotArray()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $this->expectExceptionMessage('Filters must be an array. Value was "some-string"');

        self::$client->request('GET', '/media/cache/resolve/thumbnail_web_path/rc/invalidHash/images/cats.jpeg?'.http_build_query([
            'filters' => 'some-string',
            '_hash' => 'hash',
        ]));
    }

    public function testShouldThrowNotFoundHttpExceptionIfFileNotExists()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $this->expectExceptionMessage('Source image for path "images/shrodinger_cats_which_not_exist.jpeg" could not be found');

        self::$client->request('GET', '/media/cache/resolve/thumbnail_web_path/images/shrodinger_cats_which_not_exist.jpeg');
    }

    public function testInvalidFilterShouldThrowNotFoundHttpException()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        self::$client->request('GET', '/media/cache/resolve/invalid-filter/images/cats.jpeg');
    }

    public function testShouldResolveWithCustomFiltersPopulatingCacheFirst()
    {
        /** @var Signer $signer */
        $signer = self::$kernel->getContainer()->get('liip_imagine.cache.signer');

        $params = [
            'filters' => [
                'thumbnail' => ['size' => [50, 50]],
            ],
        ];

        $path = 'images/cats.jpeg';

        $hash = $signer->sign($path, $params['filters']);

        $expectedCachePath = 'thumbnail_web_path/rc/'.$hash.'/'.$path;

        $url = 'http://localhost/media/cache/resolve/'.$expectedCachePath.'?'.http_build_query($params);

        //guard
        $this->assertFileNotExists($this->cacheRoot.'/'.$expectedCachePath);

        self::$client->request('GET', $url);

        $response = self::$client->getResponse();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('http://localhost/media/cache/'.$expectedCachePath, $response->getTargetUrl());

        $this->assertFileExists($this->cacheRoot.'/'.$expectedCachePath);
    }

    public function testShouldResolveWithCustomFiltersFromCache()
    {
        /** @var Signer $signer */
        $signer = self::$kernel->getContainer()->get('liip_imagine.cache.signer');

        $params = [
            'filters' => [
                'thumbnail' => ['size' => [50, 50]],
            ],
        ];

        $path = 'images/cats.jpeg';

        $hash = $signer->sign($path, $params['filters']);

        $expectedCachePath = 'thumbnail_web_path/rc/'.$hash.'/'.$path;

        $url = 'http://localhost/media/cache/resolve/'.$expectedCachePath.'?'.http_build_query($params);

        $this->filesystem->dumpFile(
            $this->cacheRoot.'/'.$expectedCachePath,
            'anImageContent'
        );

        self::$client->request('GET', $url);

        $response = self::$client->getResponse();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('http://localhost/media/cache'.'/'.$expectedCachePath, $response->getTargetUrl());

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
        self::$client->request('GET', '/media/cache/resolve/thumbnail_web_path/images/'.$urlEncodedFileName.'.jpeg');

        $response = self::$client->getResponse();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('http://localhost/media/cache/thumbnail_web_path/images/foo%20bar.jpeg', $response->getTargetUrl());

        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/foo bar.jpeg');
    }
}
