<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Functional\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\Resolver\FormatExtensionResolver;
use Liip\ImagineBundle\Tests\Functional\AbstractSetupWebTestCase;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Factory\Resolver\FormatExtensionResolverFactory
 * @covers \Liip\ImagineBundle\Imagine\Cache\Resolver\FormatExtensionResolver
 */
class FormatExtensionResolverTest extends AbstractSetupWebTestCase
{
    public function testCouldBeGetFromContainer(): void
    {
        $this->assertInstanceOf(
            FormatExtensionResolver::class,
            self::$kernel->getContainer()->get('liip_imagine.cache.resolver.format_extension')
        );
    }

    public function testStoresTheFilteredImageWithTheExtensionOfTheFormat(): void
    {
        $container = static::getContainer();

        // a filter set of its own, so that the commands tests iterating all filter sets are not affected
        $filterConfiguration = $container->get('liip_imagine.filter.configuration');
        $filterConfiguration->set('thumbnail_png', array_merge($filterConfiguration->get('thumbnail_default'), [
            'cache' => 'format_extension',
            'format' => 'png',
        ]));

        $url = $container->get('liip_imagine.service.filter')->getUrlOfFilteredImage('images/cats.jpeg', 'thumbnail_png');

        $this->assertSame('http://localhost/media/cache/thumbnail_png/images/cats.png', $url);
        $this->assertFileExists($this->cacheRoot.'/thumbnail_png/images/cats.png');
        $this->assertFileDoesNotExist($this->cacheRoot.'/thumbnail_png/images/cats.jpeg');
        $this->assertSame('image/png', mime_content_type($this->cacheRoot.'/thumbnail_png/images/cats.png'));
    }
}
