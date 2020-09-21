<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\Resolver\FormatExtensionResolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Liip\ImagineBundle\Imagine\Cache\Resolver\FormatExtensionResolver
 */
class FormatExtensionResolverTest extends AbstractTest
{
    /**
     * @var MockObject|ResolverInterface
     */
    private $primaryResolver;

    /**
     * @var FormatExtensionResolver
     */
    private $resolver;

    protected function setUp(): void
    {
        $this->primaryResolver = $this->createObjectMock(ResolverInterface::class);
        $filterConfiguration = new FilterConfiguration([
            'thumbnail' => [
                'format' => 'webp',
            ],
            'thumbnail_jpeg' => [
                'format' => 'jpeg',
            ],
        ]);
        $this->resolver = new FormatExtensionResolver($this->primaryResolver, $filterConfiguration);
    }

    public function providePaths(): array
    {
        return [
            ['foo/bar.png', 'foo/bar.webp'],
            ['foo/bar', 'foo/bar.webp'],
            ['foo.png', 'foo.webp'],
            ['foo', 'foo.webp'],
            ['foo.bar/baz.png', 'foo.bar/baz.webp'],
            ['foo.bar/baz', 'foo.bar/baz.webp'],
        ];
    }

    /**
     * @dataProvider providePaths
     */
    public function testResolve(string $path, string $expectedPath): void
    {
        $filter = 'thumbnail';

        $this->primaryResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($expectedPath, $filter);

        $this->resolver->resolve($path, $filter);
    }

    /**
     * @dataProvider providePaths
     */
    public function testStore(string $path, string $expectedPath): void
    {
        $filter = 'thumbnail';
        $binary = new Binary('aContent', 'image/webp', 'webp');

        $this->primaryResolver
            ->expects($this->once())
            ->method('store')
            ->with($binary, $expectedPath, $filter);

        $this->resolver->store($binary, $path, $filter);
    }

    /**
     * @dataProvider providePaths
     */
    public function testIsStored(string $path, string $expectedPath): void
    {
        $filter = 'thumbnail';

        $this->primaryResolver
            ->expects($this->once())
            ->method('isStored')
            ->with($expectedPath, $filter);

        $this->resolver->isStored($path, $filter);
    }

    public function testRemove(): void
    {
        $filters = ['thumbnail', 'thumbnail_jpeg'];
        $paths = [
            'foo/bar.png',
            'foo/bar',
            'foo.png',
            'foo',
            'foo.bar/baz.png',
            'foo.bar/baz',
        ];
        $expectedPaths = [
            'foo/bar.webp',
            'foo/bar.jpeg',
            'foo.webp',
            'foo.jpeg',
            'foo.bar/baz.webp',
            'foo.bar/baz.jpeg',
        ];

        $this->primaryResolver
            ->expects($this->once())
            ->method('remove')
            ->with($expectedPaths, $filters);

        $this->resolver->remove($paths, $filters);
    }
}
