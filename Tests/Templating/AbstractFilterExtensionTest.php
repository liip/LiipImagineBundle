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
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Loader\ArrayLoader;

abstract class AbstractFilterExtensionTest extends AbstractTest
{
    public function testAddsFilterMethodToFiltersList(): void
    {
        $this->assertCount(2, $this->createExtension($this->createCacheManagerMock())->getFilters());
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

    public function testRenderImagineFilterCacheWithRuntimeConfig(): void
    {
        $runtimeConfig = ['thumbnail' => ['size' => [50, 50]]];

        $manager = $this->createCacheManagerMock();
        $manager
            ->expects($this->once())
            ->method('getRuntimePath')
            ->with('images/cats.jpeg', $runtimeConfig)
            ->willReturn('images/cats.jpeg/rc/hash');
        $manager
            ->expects($this->once())
            ->method('resolve')
            ->with('images/cats.jpeg/rc/hash', 'thumbnail', null)
            ->willReturn('http://localhost/media/cache/thumbnail/images/cats.jpeg/rc/hash');

        $html = $this->createTwig(
            $manager,
            "{{ 'images/cats.jpeg'|imagine_filter_cache('thumbnail', {'thumbnail': {'size': [50, 50]}}) }}"
        )->render('template');

        $this->assertSame('http://localhost/media/cache/thumbnail/images/cats.jpeg/rc/hash', $html);
    }

    abstract protected function createExtension(CacheManager $manager): AbstractExtension;

    protected function createTwig(CacheManager $manager, string $template): Environment
    {
        $twig = new Environment(new ArrayLoader(['template' => $template]), ['strict_variables' => true]);
        $twig->addExtension($this->createExtension($manager));

        return $twig;
    }
}
