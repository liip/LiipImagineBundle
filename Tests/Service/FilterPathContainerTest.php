<?php
declare(strict_types=1);

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
        $source = 'cats.jpeg';
        $options = [];

        $container = new FilterPathContainer($source);

        $this->assertSame($source, $container->getSource());
        $this->assertSame($source, $container->getTarget());
        $this->assertSame($options, $container->getOptions());
    }

    public function testCustomTarget(): void
    {
        $source = 'cats.jpeg';
        $target = 'cats.jpeg.webp';
        $options = [
            'format' => 'webp',
        ];

        $container = new FilterPathContainer($source, $target, $options);

        $this->assertSame($source, $container->getSource());
        $this->assertSame($target, $container->getTarget());
        $this->assertSame($options, $container->getOptions());
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
        $source = 'cats.jpeg';
        $target = 'cats.jpeg.webp';

        $container = (new FilterPathContainer($source, '', $baseOptions))->createWebp($webpOptions);

        $this->assertSame($source, $container->getSource());
        $this->assertSame($target, $container->getTarget());
        $this->assertSame($expectedOptions, $container->getOptions());
    }
}
