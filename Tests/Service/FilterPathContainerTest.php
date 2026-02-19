<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Service;

use Liip\ImagineBundle\Service\FilterPathContainer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Service\FilterPathContainer
 */
final class FilterPathContainerTest extends TestCase
{
    public function testWithEmptyTarget(): void
    {
        $source = 'images/cats.jpeg';
        $options = [];

        $container = new FilterPathContainer($source);

        $this->assertSame($source, $container->getSource());
        $this->assertSame($source, $container->getTarget());
        $this->assertSame($options, $container->getOptions());
    }

    public function testCustomTarget(): void
    {
        $source = 'images/cats.jpeg';
        $target = 'images/cats.jpeg.webp';
        $options = [
            'format' => 'webp',
        ];

        $container = new FilterPathContainer($source, $target, $options);

        $this->assertSame($source, $container->getSource());
        $this->assertSame($target, $container->getTarget());
        $this->assertSame($options, $container->getOptions());
    }

    public function provideAlternativeOptions(): \Traversable
    {
        yield 'avif options' => [
            'avif',
            [],
            [],
            [
                'format' => 'avif',
            ],
            'images/cats.jpeg.avif',
        ];

        yield 'avif with use_default_driver false' => [
            'avif',
            [],
            [
                'use_default_driver' => false,
            ],
            [],
            'images/cats.jpeg.avif',
        ];

        yield 'custom avif options' => [
            'avif',
            [],
            [
                'quality' => 90,
            ],
            [
                'format' => 'avif',
                'quality' => 90,
            ],
            'images/cats.jpeg.avif',
        ];

        yield 'overwrite base options with avif' => [
            'avif',
            [
                'format' => 'jpeg',
                'quality' => 80,
            ],
            [
                'quality' => 70,
            ],
            [
                'format' => 'avif',
                'quality' => 70,
            ],
            'images/cats.jpeg.avif',
        ];
    }

    /**
     * @dataProvider provideAlternativeOptions
     */
    public function testCreateAlternative(string $format, array $baseOptions, array $altOptions, array $expectedOptions, string $expectedTarget): void
    {
        $source = 'images/cats.jpeg';

        $container = (new FilterPathContainer($source, '', $baseOptions))->createAlternative($format, $altOptions);

        $this->assertSame($source, $container->getSource());
        $this->assertSame($expectedTarget, $container->getTarget());
        $this->assertSame($expectedOptions, $container->getOptions());
    }

    public function provideWebpOptions(): \Traversable
    {
        yield 'empty options' => [
            [],
            [],
            [
                'format' => 'webp',
            ],
        ];

        yield 'custom webp options' => [
            [],
            [
                'quality' => 100,
            ],
            [
                'format' => 'webp',
                'quality' => 100,
            ],
        ];

        yield 'overwrite base options' => [
            [
                'format' => 'jpeg',
                'quality' => 80,
                'jpeg_quality' => 80,
                'post_processors' => [
                    'jpegoptim' => [
                        'strip_all' => true,
                        'max' => 80,
                        'progressive' => true,
                    ],
                ],
            ],
            [
                'quality' => 100,
                'post_processors' => [
                    'my_custom_webp_post_processor' => [],
                ],
            ],
            [
                'format' => 'webp',
                'quality' => 100,
                'post_processors' => [
                    'my_custom_webp_post_processor' => [],
                ],
                'jpeg_quality' => 80,
            ],
        ];
    }

    /**
     * @dataProvider provideWebpOptions
     */
    public function testCreateWebp(array $baseOptions, array $webpOptions, array $expectedOptions): void
    {
        $source = 'images/cats.jpeg';
        $target = 'images/cats.jpeg.webp';

        $container = (new FilterPathContainer($source, '', $baseOptions))->createWebp($webpOptions);

        $this->assertSame($source, $container->getSource());
        $this->assertSame($target, $container->getTarget());
        $this->assertSame($expectedOptions, $container->getOptions());
    }
}
