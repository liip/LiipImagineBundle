<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Form\Type;

use Liip\ImagineBundle\Templating\LazyFilterExtension;
use Liip\ImagineBundle\Templating\LazyFilterRuntime;
use Liip\ImagineBundle\Tests\AbstractTest;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

/**
 * Renders the "liip_imagine_image_widget" block of the bundled form theme with the installed Twig version.
 *
 * @coversNothing
 */
class ImageTypeThemeTest extends AbstractTest
{
    public function testRenderWidgetWithLink(): void
    {
        $html = $this->renderWidget([
            'image_path' => 'images/cats.jpeg',
            'image_filter' => 'thumbnail',
            'image_attr' => ['alt' => 'cat'],
            'link_url' => 'images/cats.jpeg',
            'link_filter' => 'big',
            'link_attr' => ['target' => '_blank'],
        ]);

        $this->assertSame(
            '<div> <a href="/media/cache/big/images/cats.jpeg" target="_blank" > <img src="/media/cache/thumbnail/images/cats.jpeg" alt="cat" /> </a> </div> <input>',
            $html
        );
    }

    public function testRenderWidgetWithUnfilteredLink(): void
    {
        $html = $this->renderWidget([
            'image_path' => 'images/cats.jpeg',
            'image_filter' => 'thumbnail',
            'image_attr' => [],
            'link_url' => 'https://example.com/cats',
            'link_filter' => null,
            'link_attr' => [],
        ]);

        $this->assertSame(
            '<div> <a href="https://example.com/cats" > <img src="/media/cache/thumbnail/images/cats.jpeg" /> </a> </div> <input>',
            $html
        );
    }

    public function testRenderWidgetWithoutImage(): void
    {
        $html = $this->renderWidget([
            'image_path' => null,
            'image_filter' => 'thumbnail',
            'image_attr' => [],
            'link_url' => null,
            'link_filter' => null,
            'link_attr' => [],
        ]);

        $this->assertSame('<input>', $html);
    }

    private function renderWidget(array $vars): string
    {
        $manager = $this->createCacheManagerMock();
        $manager
            ->method('getBrowserPath')
            ->willReturnCallback(static function ($path, $filter) {
                return '/media/cache/'.$filter.'/'.$path;
            });

        $twig = new Environment(new ArrayLoader([
            'form_div_layout.html.twig' => file_get_contents(__DIR__.'/../../../Resources/views/Form/form_div_layout.html.twig'),
            'layout.html.twig' => "{{ block('liip_imagine_image_widget') }}",
            // stands in for the Symfony form theme which provides the "form_widget_simple" block
            'theme.html.twig' => "{% extends 'layout.html.twig' %}{% use 'form_div_layout.html.twig' %}{% block form_widget_simple %}<input>{% endblock %}",
        ]), ['strict_variables' => true]);
        $twig->addExtension(new LazyFilterExtension());
        $twig->addRuntimeLoader(new FactoryRuntimeLoader([
            LazyFilterRuntime::class => static function () use ($manager) {
                return new LazyFilterRuntime($manager);
            },
        ]));

        $html = $twig->render('theme.html.twig', $vars);

        return trim(preg_replace('/\s+/', ' ', $html));
    }
}
