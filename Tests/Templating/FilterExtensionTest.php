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
use Liip\ImagineBundle\Templating\FilterExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Loader\ArrayLoader;

/**
 * @covers \Liip\ImagineBundle\Templating\FilterTrait
 * @covers \Liip\ImagineBundle\Templating\FilterExtension
 *
 * @group legacy
 */
class FilterExtensionTest extends AbstractFilterTest
{
    public function testAddsFilterMethodToFiltersList(): void
    {
        $this->assertCount(2, $this->createTemplatingMock()->getFilters());
    }

    public function testInstanceOfTwigFilter(): void
    {
        $this->assertInstanceOf(AbstractExtension::class, $this->createTemplatingMock());
    }

    public function testRenderFilters(): void
    {
        $manager = $this->createCacheManagerMock();
        $manager
            ->expects($this->once())
            ->method('getBrowserPath')
            ->with('images/cats.jpeg', 'thumbnail', [], null, UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://localhost/media/cache/thumbnail/images/cats.jpeg');
        $manager
            ->expects($this->once())
            ->method('resolve')
            ->with('images/dogs.jpeg', 'thumbnail', null)
            ->willReturn('http://localhost/media/cache/thumbnail/images/dogs.jpeg');

        $twig = new Environment(new ArrayLoader([
            'template' => "{{ 'images/cats.jpeg'|imagine_filter('thumbnail') }} {{ 'images/dogs.jpeg'|imagine_filter_cache('thumbnail') }}",
        ]), ['strict_variables' => true]);
        $twig->addExtension($this->createTemplatingMock($manager));

        $this->assertSame(
            'http://localhost/media/cache/thumbnail/images/cats.jpeg http://localhost/media/cache/thumbnail/images/dogs.jpeg',
            $twig->render('template')
        );
    }

    protected function createTemplatingMock(?CacheManager $manager = null): FilterExtension
    {
        if (!class_exists(AbstractExtension::class)) {
            $this->markTestSkipped('Requires the twig/twig package.');
        }

        $mock = new FilterExtension($manager ?: $this->createCacheManagerMock());

        $this->assertInstanceOf(FilterExtension::class, $mock);

        return $mock;
    }
}
