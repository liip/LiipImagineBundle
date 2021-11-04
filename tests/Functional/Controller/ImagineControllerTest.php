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
    /**
     * PHP compiled with WebP support.
     *
     * @var bool
     */
    private $webp_generate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->webp_generate = \function_exists('imagewebp');

        // We turn on generation through reflection, since only in runtime we can determine whether the WebP is
        // supported by the current PHP build or not. Enabling WebP in configurations will drop all tests if WebP is
        // not supported.
        if ($this->webp_generate) {
            $filterService = self::getService('liip_imagine.service.filter');
            $webpGenerate = new \ReflectionProperty($filterService, 'webpGenerate');
            $webpGenerate->setAccessible(true);
            $webpGenerate->setValue($filterService, true);
        }
    }

    public function testCouldBeGetFromContainer(): void
    {
        $this->assertInstanceOf(ImagineController::class, self::$kernel->getContainer()->get(ImagineController::class));
    }

    public function provideImageNames(): iterable
    {
        yield 'regular' => ['image' => 'cats.jpeg', 'urlimage' => 'cats.jpeg'];
        yield 'whitespace' => ['image' => 'white cat.jpeg', 'urlimage' => 'white%20cat.jpeg'];
        yield 'plus' => ['image' => 'cat+plus.jpeg', 'urlimage' => 'cat%2Bplus.jpeg'];
        yield 'questionmark' => ['image' => 'cat?question.jpeg', 'urlimage' => 'cat%3Fquestion.jpeg'];
        yield 'hash' => ['image' => 'cat#hash.jpeg', 'urlimage' => 'cat%23hash.jpeg'];
    }

    /**
     * @dataProvider provideImageNames
     *
     * @param string $image
     */
    public function testShouldResolvePopulatingCacheFirst($image, $urlimage): void
    {
        //guard
        $this->assertFileDoesNotExist($this->cacheRoot.'/thumbnail_web_path/images/'.$image);

        $this->client->request('GET', '/media/cache/resolve/thumbnail_web_path/images/'.$urlimage);

        $response = $this->client->getResponse();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('http://localhost/media/cache/thumbnail_web_path/images/'.$urlimage, $response->getTargetUrl());

        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/'.$image);

        // PHP compiled with WebP support
        if ($this->webp_generate) {
            $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/'.$image.'.webp');
        }
    }

    public function testShouldResolvePopulatingCacheFirstWebP(): void
    {
        //guard
        $this->assertFileDoesNotExist($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');

        $this->client->request('GET', '/media/cache/resolve/thumbnail_web_path/images/cats.jpeg', [], [], [
            // Accept from Google Chrome 86
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
        ]);

        $response = $this->client->getResponse();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());

        // PHP compiled with WebP support
        if ($this->webp_generate) {
            $this->assertSame('http://localhost/media/cache/thumbnail_web_path/images/cats.jpeg.webp', $response->getTargetUrl());
            $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg.webp');
        } else {
            $this->assertSame('http://localhost/media/cache/thumbnail_web_path/images/cats.jpeg', $response->getTargetUrl());
        }

        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
    }

    public function testShouldResolveFromCache(): void
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg',
            'anImageContent'
        );

        $this->client->request('GET', '/media/cache/resolve/thumbnail_web_path/images/cats.jpeg');

        $response = $this->client->getResponse();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('http://localhost/media/cache/thumbnail_web_path/images/cats.jpeg', $response->getTargetUrl());

        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
    }

    public function testShouldResolveWebPFromCache(): void
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg.webp',
            'anImageContentWebP'
        );

        $this->client->request('GET', '/media/cache/resolve/thumbnail_web_path/images/cats.jpeg', [], [], [
            // Accept from Google Chrome 86
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
        ]);

        $response = $this->client->getResponse();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());

        // PHP compiled with WebP support
        if ($this->webp_generate) {
            $this->assertSame('http://localhost/media/cache/thumbnail_web_path/images/cats.jpeg.webp', $response->getTargetUrl());
        } else {
            $this->assertSame('http://localhost/media/cache/thumbnail_web_path/images/cats.jpeg', $response->getTargetUrl());
        }

        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg.webp');
    }

    public function testThrowBadRequestIfSignInvalidWhileUsingCustomFilters(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\BadRequestHttpException::class);
        $this->expectExceptionMessage('Signed url does not pass the sign check for path "images/cats.jpeg" and filter "thumbnail_web_path" and runtime config {"thumbnail":{"size":["50","50"]}}');

        $this->client->request('GET', '/media/cache/resolve/thumbnail_web_path/rc/invalidHash/images/cats.jpeg?'.http_build_query([
            'filters' => [
                'thumbnail' => ['size' => [50, 50]],
            ],
            '_hash' => 'invalid',
        ]));
    }

    public function testShouldThrowNotFoundHttpExceptionIfFiltersNotArray(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $this->expectExceptionMessage('Filters must be an array. Value was "some-string"');

        $this->client->request('GET', '/media/cache/resolve/thumbnail_web_path/rc/invalidHash/images/cats.jpeg?'.http_build_query([
            'filters' => 'some-string',
            '_hash' => 'hash',
        ]));
    }

    public function testShouldThrowNotFoundHttpExceptionIfFileNotExists(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $this->expectExceptionMessage('Source image for path "images/shrodinger_cats_which_not_exist.jpeg" could not be found');

        $this->client->request('GET', '/media/cache/resolve/thumbnail_web_path/images/shrodinger_cats_which_not_exist.jpeg');
    }

    public function testInvalidFilterShouldThrowNotFoundHttpException(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $this->client->request('GET', '/media/cache/resolve/invalid-filter/images/cats.jpeg');
    }

    public function testShouldResolveWithCustomFiltersPopulatingCacheFirst(): void
    {
        /** @var Signer $signer */
        $signer = self::getService('liip_imagine.cache.signer');

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
        $this->assertFileDoesNotExist($this->cacheRoot.'/'.$expectedCachePath);

        $this->client->request('GET', $url);

        $response = $this->client->getResponse();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('http://localhost/media/cache/'.$expectedCachePath, $response->getTargetUrl());

        $this->assertFileExists($this->cacheRoot.'/'.$expectedCachePath);

        // PHP compiled with WebP support
        if ($this->webp_generate) {
            $this->assertFileExists($this->cacheRoot.'/'.$expectedCachePath.'.webp');
        }
    }

    public function testShouldResolveWithCustomFiltersPopulatingCacheFirstWebP(): void
    {
        /** @var Signer $signer */
        $signer = self::getService('liip_imagine.cache.signer');

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
        $this->assertFileDoesNotExist($this->cacheRoot.'/'.$expectedCachePath);

        $this->client->request('GET', $url, [], [], [
            // Accept from Google Chrome 86
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
        ]);

        $response = $this->client->getResponse();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());

        // PHP compiled with WebP support
        if ($this->webp_generate) {
            $this->assertSame('http://localhost/media/cache/'.$expectedCachePath.'.webp', $response->getTargetUrl());
            $this->assertFileExists($this->cacheRoot.'/'.$expectedCachePath.'.webp');
        } else {
            $this->assertSame('http://localhost/media/cache/'.$expectedCachePath, $response->getTargetUrl());
        }

        $this->assertFileExists($this->cacheRoot.'/'.$expectedCachePath);
    }

    public function testShouldResolveWithCustomFiltersFromCache(): void
    {
        /** @var Signer $signer */
        $signer = self::getService('liip_imagine.cache.signer');

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

        $this->client->request('GET', $url);

        $response = $this->client->getResponse();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('http://localhost/media/cache'.'/'.$expectedCachePath, $response->getTargetUrl());

        $this->assertFileExists($this->cacheRoot.'/'.$expectedCachePath);

        // PHP compiled with WebP support
        if ($this->webp_generate) {
            $this->assertFileExists($this->cacheRoot.'/'.$expectedCachePath.'.webp');
        }
    }

    public function testShouldResolvePathWithSpecialCharactersAndWhiteSpaces(): void
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/foo bar.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/foo bar.jpeg.webp',
            'anImageContentWebP'
        );

        // we are calling url with encoded file name as it will be called by browser
        $urlEncodedFileName = 'foo%20bar';
        $this->client->request('GET', '/media/cache/resolve/thumbnail_web_path/images/'.$urlEncodedFileName.'.jpeg');

        $response = $this->client->getResponse();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('http://localhost/media/cache/thumbnail_web_path/images/foo%20bar.jpeg', $response->getTargetUrl());

        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/foo bar.jpeg');

        // PHP compiled with WebP support
        if ($this->webp_generate) {
            $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/foo bar.jpeg.webp');
        }
    }
}
