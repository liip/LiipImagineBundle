<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Functional\Bridge\SymfonyUx;

use Liip\ImagineBundle\Bridge\SymfonyUx\UxImage;
use Liip\ImagineBundle\Tests\Functional\AbstractSetupWebTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\UX\Image\Exception\InvalidArgumentException;
use Symfony\UX\Image\Fit;
use Symfony\UX\Image\ImageUrlGenerator;

/**
 * @covers \Liip\ImagineBundle\Bridge\SymfonyUx\ImageProvider
 * @covers \Liip\ImagineBundle\Bridge\SymfonyUx\ImageProviderFactory
 */
class ImageProviderTest extends AbstractSetupWebTestCase
{
    protected function setUp(): void
    {
        if (!UxImage::isInstalled()) {
            $this->markTestSkipped('Requires the symfony/ux-image package.');
        }

        parent::setUp();
    }

    public function testTheProviderIsTheOneTheDsnNames(): void
    {
        $this->assertSame('liipimagine', $this->getService('test.ux_image.provider')->getName());
    }

    public function testTheBundleDeclaresAFilterSetPerFormatTheProviderSupports(): void
    {
        $filterSets = $this->getParameter('liip_imagine.filter_sets');

        $this->assertArrayHasKey('ux_image', $filterSets);
        $this->assertNull($filterSets['ux_image']['format']);

        foreach (['webp', 'jpeg', 'png'] as $format) {
            $this->assertSame($format, $filterSets['ux_image_'.$format]['format']);
        }
    }

    public function testGeneratesASignedRuntimeFilterUrl(): void
    {
        $url = $this->getUrlGenerator()->generate('/images/cats.jpeg', 800, 600);

        $this->assertStringStartsWith('http://localhost/media/cache/resolve/ux_image/rc/', $url);

        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        $this->assertSame(['fixed' => ['width' => '800', 'height' => '600']], $query['filters']);
    }

    public function testAFormatGeneratesThroughItsOwnFilterSet(): void
    {
        $url = $this->getUrlGenerator()->generate('/images/cats.jpeg', 800, 600, format: 'png');

        $this->assertStringStartsWith('http://localhost/media/cache/resolve/ux_image_png/rc/', $url);
    }

    public function testGeneratesAPlainFilterUrlWithoutATransformation(): void
    {
        $this->assertSame(
            'http://localhost/media/cache/resolve/ux_image/images/cats.jpeg',
            $this->getUrlGenerator()->generate('/images/cats.jpeg')
        );
    }

    public function testAnOperationSelectsAnotherFilterSet(): void
    {
        $url = $this->getUrlGenerator()->generate('/images/cats.jpeg', 200, 200, operations: ['liipimagine' => ['filter' => 'thumbnail_web_path']]);

        $this->assertStringStartsWith('http://localhost/media/cache/resolve/thumbnail_web_path/rc/', $url);
    }

    /**
     * @return array[]
     */
    public function provideTransformations(): array
    {
        return [
            'cover crops to the requested box' => [['width' => 50, 'height' => 40], [50, 40], 'image/jpeg'],
            'contain fits inside the requested box' => [['width' => 50, 'height' => 40, 'fit' => 'contain'], [50, 25], 'image/jpeg'],
            'a width alone keeps the aspect ratio' => [['width' => 32], [32, 16], 'image/jpeg'],
            'a height alone keeps the aspect ratio' => [['height' => 30], [60, 30], 'image/jpeg'],
            'upscaling is allowed' => [['width' => 2000, 'height' => 1600], [2000, 1600], 'image/jpeg'],
            'a format renders through its own filter set' => [['width' => 50, 'height' => 40, 'format' => 'png'], [50, 40], 'image/png'],
        ];
    }

    /**
     * @dataProvider provideTransformations
     *
     * @param int[] $expectedSize
     */
    public function testServesTheTransformedImage(array $arguments, array $expectedSize, string $expectedMimeType): void
    {
        $url = $this->getUrlGenerator()->generate(
            '/images/cats.jpeg',
            $arguments['width'] ?? null,
            $arguments['height'] ?? null,
            isset($arguments['fit']) ? Fit::from($arguments['fit']) : null,
            $arguments['format'] ?? null
        );

        $this->client->request('GET', $url);
        $response = $this->client->getResponse();

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $cachedFile = $this->webRoot.parse_url($response->getTargetUrl(), PHP_URL_PATH);
        $this->assertFileExists($cachedFile);

        $size = getimagesize($cachedFile);

        $this->assertSame($expectedSize, [$size[0], $size[1]]);
        $this->assertSame($expectedMimeType, $size['mime']);
    }

    public function testRefusesATamperedRuntimeConfiguration(): void
    {
        $url = $this->getUrlGenerator()->generate('/images/cats.jpeg', 50, 40);
        $tampered = str_replace('width%5D=50', 'width%5D=5000', $url);

        $this->assertNotSame($url, $tampered, 'The runtime configuration to tamper with must be part of the url.');

        $this->expectException(BadRequestHttpException::class);

        $this->client->request('GET', $tampered);
    }

    public function testRejectsAFormatTheProviderDoesNotSupport(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('{The image format "tiff" is not supported by the "liipimagine" provider}');

        $this->getUrlGenerator()->generate('/images/cats.jpeg', 800, format: 'tiff');
    }

    public function testRejectsAnUnsupportedOperation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('{The image operation "gravity" is not supported by the "liipimagine" provider}');

        $this->getUrlGenerator()->generate('/images/cats.jpeg', 800, operations: ['liipimagine' => ['gravity' => 'north']]);
    }

    private function getUrlGenerator(): ImageUrlGenerator
    {
        return $this->getService('test.ux_image.url_generator');
    }
}
