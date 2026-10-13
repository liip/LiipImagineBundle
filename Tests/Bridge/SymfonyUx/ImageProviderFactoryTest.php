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

use Liip\ImagineBundle\Bridge\SymfonyUx\ImageProviderFactory;
use Liip\ImagineBundle\Bridge\SymfonyUx\UxImage;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\Image\Exception\InvalidArgumentException;
use Symfony\UX\Image\ImageTransformation;
use Symfony\UX\Image\Provider\Dsn;

/**
 * @covers \Liip\ImagineBundle\Bridge\SymfonyUx\ImageProviderFactory
 */
class ImageProviderFactoryTest extends AbstractTest
{
    protected function setUp(): void
    {
        if (!UxImage::isInstalled()) {
            $this->markTestSkipped('Requires the symfony/ux-image package.');
        }

        parent::setUp();
    }

    /**
     * @return array[]
     */
    public function provideSchemes(): array
    {
        return [
            ['liipimagine://ux_image', true],
            ['liipimagine://ux_image?resolver=s3', true],
            ['cloudflare://cdn.example.com', false],
            ['null://null', false],
        ];
    }

    /**
     * @dataProvider provideSchemes
     */
    public function testSupportsOnlyItsOwnScheme(string $dsn, bool $expected): void
    {
        $this->assertSame($expected, $this->createFactory()->supports(new Dsn($dsn)));
    }

    public function testDefaultsEverythingButTheFilterSet(): void
    {
        $provider = $this->createFactory()->create(new Dsn('liipimagine://ux_image'));

        $this->assertSame(['webp', 'jpeg', 'png'], $provider->getSupportedFormats());
        $this->assertFalse($provider->supportsAutoFormat());
    }

    public function testTakesTheFilterSetFromTheHost(): void
    {
        $this->assertGeneratesWith('liipimagine://hero', 'hero', null, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * A host-less DSN has to be written without the "//", which parse_url() rejects on its own.
     */
    public function testFallsBackToTheDefaultFilterSetWithoutAHost(): void
    {
        $this->assertGeneratesWith('liipimagine:', 'ux_image', null, UrlGeneratorInterface::ABSOLUTE_URL);
        $this->assertGeneratesWith('liipimagine:?resolver=s3', 'ux_image', 's3', UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function testReadsTheResolverAndReferenceTypeFromTheDsn(): void
    {
        $this->assertGeneratesWith(
            'liipimagine://hero?resolver=s3&reference_type=absolute_path',
            'hero',
            's3',
            UrlGeneratorInterface::ABSOLUTE_PATH
        );
    }

    public function testReadsTheFormatsFromTheDsn(): void
    {
        $provider = $this->createFactory()->create(new Dsn('liipimagine://ux_image?formats=avif,%20webp'));

        $this->assertSame(['avif', 'webp'], $provider->getSupportedFormats());
    }

    public function testAutoFormatDefaultsToWebpGeneration(): void
    {
        $factory = new ImageProviderFactory($this->createCacheManagerMock(), $this->createUxImageFilterConfiguration(), 'ux_image', true);

        $this->assertTrue($factory->create(new Dsn('liipimagine://ux_image'))->supportsAutoFormat());
    }

    public function testAutoFormatOfTheDsnWinsOverWebpGeneration(): void
    {
        $factory = new ImageProviderFactory($this->createCacheManagerMock(), $this->createUxImageFilterConfiguration(), 'ux_image', true);

        $this->assertFalse($factory->create(new Dsn('liipimagine://ux_image?auto_format=false'))->supportsAutoFormat());
    }

    /**
     * @return array[]
     */
    public function provideInvalidDsns(): array
    {
        return [
            'unknown option' => ['liipimagine://ux_image?gravity=north', 'The option "gravity" does not exist'],
            'unknown reference type' => ['liipimagine://ux_image?reference_type=nope', 'The option "reference_type" with value "nope" is invalid'],
            'unreadable auto format' => ['liipimagine://ux_image?auto_format=yes', 'The option "auto_format" with value "yes" is invalid'],
            'empty format list' => ['liipimagine://ux_image?formats=', 'the "formats" option must name at least one format'],
        ];
    }

    /**
     * @dataProvider provideInvalidDsns
     */
    public function testRejectsAnInvalidDsn(string $dsn, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatchesBC('{'.preg_quote($expectedMessage, '}').'}');

        $this->createFactory()->create(new Dsn($dsn));
    }

    private function assertGeneratesWith(string $dsn, string $filterSet, ?string $resolver, int $referenceType): void
    {
        $cacheManager = $this->createCacheManagerMock();
        $cacheManager
            ->method('getBrowserPath')
            ->with('/images/cats.jpeg', $filterSet, [], $resolver, $referenceType)
            ->willReturn('/the-url');

        $provider = $this->createFactory($cacheManager)->create(new Dsn($dsn));

        $this->assertSame('/the-url', $provider->generateUrl(new ImageTransformation('/images/cats.jpeg')));
    }

    /**
     * @param CacheManager|MockObject|null $cacheManager
     */
    private function createFactory($cacheManager = null): ImageProviderFactory
    {
        return new ImageProviderFactory($cacheManager ?? $this->createCacheManagerMock(), $this->createUxImageFilterConfiguration(), 'ux_image', false);
    }

    private function createUxImageFilterConfiguration(): FilterConfiguration
    {
        return new FilterConfiguration(['ux_image' => [], 'hero' => []]);
    }
}
