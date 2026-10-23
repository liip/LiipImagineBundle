<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Templating;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Templating\LazyFilterExtension;
use Liip\ImagineBundle\Templating\LazyFilterRuntime;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

/**
 * @covers \Liip\ImagineBundle\Templating\LazyFilterExtension
 */
class LazyFilterExtensionTest extends AbstractTest
{
    /**
     * @var LazyFilterExtension
     */
    private $extension;

    protected function setUp(): void
    {
        $this->extension = new LazyFilterExtension();
    }

    public function testAddsFilterMethodToFiltersList(): void
    {
        $this->assertCount(2, $this->extension->getFilters());
    }

    public function testRenderImagineFilter(): void
    {
        $manager = $this->createCacheManagerMock();
        $manager
            ->expects($this->once())
            ->method('getBrowserPath')
            ->with('images/cats.jpeg', 'thumbnail', [], null, UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://localhost/media/cache/thumbnail/images/cats.jpeg');

        $html = $this->createTwig($manager, "{{ 'images/cats.jpeg'|imagine_filter('thumbnail') }}")->render('template');

        $this->assertSame('http://localhost/media/cache/thumbnail/images/cats.jpeg', $html);
    }

    public function testRenderImagineFilterWithArguments(): void
    {
        $runtimeConfig = ['thumbnail' => ['size' => [50, 50]]];

        $manager = $this->createCacheManagerMock();
        $manager
            ->expects($this->once())
            ->method('getBrowserPath')
            ->with('images/cats.jpeg', 'thumbnail', $runtimeConfig, 'custom_resolver', UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn('/media/cache/thumbnail/rc/hash/images/cats.jpeg?a=1&b=2');

        $html = $this->createTwig(
            $manager,
            "{{ 'images/cats.jpeg'|imagine_filter('thumbnail', {'thumbnail': {'size': [50, 50]}}, 'custom_resolver', 1) }}"
        )->render('template');

        // the filter result is not marked as safe and gets escaped by the HTML autoescaping strategy
        $this->assertSame('/media/cache/thumbnail/rc/hash/images/cats.jpeg?a=1&amp;b=2', $html);
    }

    public function testRenderImagineFilterCache(): void
    {
        $manager = $this->createCacheManagerMock();
        $manager
            ->expects($this->once())
            ->method('resolve')
            ->with('images/cats.jpeg', 'thumbnail', null)
            ->willReturn('http://localhost/media/cache/thumbnail/images/cats.jpeg');

        $html = $this->createTwig($manager, "{{ 'images/cats.jpeg'|imagine_filter_cache('thumbnail') }}")->render('template');

        $this->assertSame('http://localhost/media/cache/thumbnail/images/cats.jpeg', $html);
    }

    private function createTwig(CacheManager $manager, string $template): Environment
    {
        $twig = new Environment(new ArrayLoader(['template' => $template]), ['strict_variables' => true]);
        $twig->addExtension($this->extension);
        $twig->addRuntimeLoader(new FactoryRuntimeLoader([
            LazyFilterRuntime::class => static function () use ($manager) {
                return new LazyFilterRuntime($manager);
            },
        ]));

        return $twig;
    }
}
