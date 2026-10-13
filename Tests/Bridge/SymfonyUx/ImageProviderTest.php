<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Bridge\SymfonyUx;

use Liip\ImagineBundle\Bridge\SymfonyUx\ImageProvider;
use Liip\ImagineBundle\Bridge\SymfonyUx\UxImage;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\Image\Exception\InvalidArgumentException;
use Symfony\UX\Image\Fit;
use Symfony\UX\Image\ImageTransformation;
use Symfony\UX\Image\Provider\ProviderInterface;

/**
 * @covers \Liip\ImagineBundle\Bridge\SymfonyUx\ImageProvider
 */
class ImageProviderTest extends AbstractTest
{
    protected function setUp(): void
    {
        if (!UxImage::isInstalled()) {
            $this->markTestSkipped('Requires the symfony/ux-image package.');
        }

        parent::setUp();
    }

    public function testReportsWhatItWasConfiguredWith(): void
    {
        $provider = $this->createProvider($this->createCacheManagerMock(), ['avif', 'webp'], true);

        $this->assertInstanceOf(ProviderInterface::class, $provider);
        $this->assertSame('liipimagine', $provider->getName());
        $this->assertSame(['filter', 'resolver'], $provider->getSupportedOperations());
        $this->assertSame(['avif', 'webp'], $provider->getSupportedFormats());
        $this->assertTrue($provider->supportsAutoFormat());
    }

    /**
     * Built from scalars rather than from an ImageTransformation so that the data set does not load
     * symfony/ux-image before setUp() had the chance to skip.
     *
     * @return array[]
     */
    public function provideTransformations(): array
    {
        return [
            'nothing to transform' => [
                [],
                [],
            ],
            'both dimensions, cover' => [
                ['width' => 800, 'height' => 600, 'fit' => 'cover'],
                ['fixed' => ['width' => 800, 'height' => 600]],
            ],
            'both dimensions, no fit' => [
                ['width' => 800, 'height' => 600],
                ['fixed' => ['width' => 800, 'height' => 600]],
            ],
            'both dimensions, contain' => [
                ['width' => 800, 'height' => 600, 'fit' => 'contain'],
                ['thumbnail' => ['size' => [800, 600], 'mode' => 'inset']],
            ],
            'width only' => [
                ['width' => 400],
                ['thumbnail' => ['size' => [400], 'mode' => 'outbound']],
            ],
            'height only' => [
                ['height' => 300],
                ['thumbnail' => ['size' => [1 => 300], 'mode' => 'outbound']],
            ],
            'a contain fit without both dimensions still insets' => [
                ['width' => 400, 'fit' => 'contain'],
                ['thumbnail' => ['size' => [400], 'mode' => 'inset']],
            ],
            'the quality has nowhere to go in a runtime configuration' => [
                ['width' => 400, 'quality' => 80],
                ['thumbnail' => ['size' => [400], 'mode' => 'outbound']],
            ],
        ];
    }

    /**
     * @dataProvider provideTransformations
     */
    public function testTranslatesTheTransformationIntoARuntimeConfiguration(array $arguments, array $expectedRuntimeConfig): void
    {
        $cacheManager = $this->createCacheManagerMock();
        $cacheManager
            ->method('getBrowserPath')
            ->with('/images/cats.jpeg', 'ux_image', $expectedRuntimeConfig, null, UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('/the-url');

        $this->assertSame('/the-url', $this->createProvider($cacheManager)->generateUrl($this->createTransformation($arguments)));
    }

    /**
     * @return array[]
     */
    public function provideFormats(): array
    {
        return [
            'no format keeps the base filter set' => [null, 'ux_image'],
            'an automatic format is left to the controller' => ['auto', 'ux_image'],
            'a format selects its own filter set' => ['webp', 'ux_image_webp'],
        ];
    }

    /**
     * @dataProvider provideFormats
     */
    public function testTheFormatSelectsTheFilterSet(?string $format, string $expectedFilterSet): void
    {
        $cacheManager = $this->createCacheManagerMock();
        $cacheManager
            ->method('getBrowserPath')
            ->with('/images/cats.jpeg', $expectedFilterSet, [], null, UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('/the-url');

        $transformation = $this->createTransformation(['format' => $format]);

        $this->assertSame('/the-url', $this->createProvider($cacheManager)->generateUrl($transformation));
    }

    public function testOperationsOverrideTheFilterSetAndResolverOfTheDsn(): void
    {
        $cacheManager = $this->createCacheManagerMock();
        $cacheManager
            ->method('getBrowserPath')
            ->with('/images/cats.jpeg', 'hero', [], 's3', UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('/the-url');

        $transformation = $this->createTransformation(['operations' => ['filter' => 'hero', 'resolver' => 's3']]);

        $this->assertSame('/the-url', $this->createProvider($cacheManager)->generateUrl($transformation));
    }

    public function testRejectsAFilterSetThatIsNotDeclared(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatchesBC('{renders through the "nope" filter set, which "liip_imagine.filter_sets" does not declare}');

        $transformation = $this->createTransformation(['operations' => ['filter' => 'nope']]);

        $this->createProvider($this->createCacheManagerMock())->generateUrl($transformation);
    }

    public function testRejectsAFormatWithoutItsOwnFilterSet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatchesBC('{renders the "avif" format through the "ux_image_avif" filter set, which "liip_imagine.filter_sets" does not declare with "format: avif"}');

        $this->createProvider($this->createCacheManagerMock(), ['avif'])->generateUrl($this->createTransformation(['format' => 'avif']));
    }

    public function testGeneratesWithTheConfiguredReferenceType(): void
    {
        $cacheManager = $this->createCacheManagerMock();
        $cacheManager
            ->method('getBrowserPath')
            ->with('/images/cats.jpeg', 'ux_image', [], null, UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn('/the-url');

        $provider = new ImageProvider($cacheManager, $this->createUxImageFilterConfiguration(), 'ux_image', null, UrlGeneratorInterface::ABSOLUTE_PATH, ['webp'], false);

        $this->assertSame('/the-url', $provider->generateUrl($this->createTransformation([])));
    }

    /**
     * @param CacheManager|MockObject $cacheManager
     * @param list<string>            $formats
     */
    private function createProvider($cacheManager, array $formats = ['webp', 'jpeg', 'png'], bool $autoFormat = false): ImageProvider
    {
        return new ImageProvider($cacheManager, $this->createUxImageFilterConfiguration(), 'ux_image', null, UrlGeneratorInterface::ABSOLUTE_URL, $formats, $autoFormat);
    }

    private function createUxImageFilterConfiguration(): FilterConfiguration
    {
        return new FilterConfiguration([
            'ux_image' => [],
            'ux_image_webp' => ['format' => 'webp'],
            'hero' => [],
        ]);
    }

    private function createTransformation(array $arguments): ImageTransformation
    {
        return new ImageTransformation(
            '/images/cats.jpeg',
            $arguments['width'] ?? null,
            $arguments['height'] ?? null,
            isset($arguments['fit']) ? Fit::from($arguments['fit']) : null,
            $arguments['format'] ?? null,
            $arguments['quality'] ?? null,
            $arguments['operations'] ?? []
        );
    }
}
