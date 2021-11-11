<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Binary\Loader;

use Doctrine\Common\Persistence\ObjectManager as LegacyObjectManager;
use Doctrine\Common\Persistence\ObjectRepository as LegacyObjectRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Liip\ImagineBundle\Binary\Loader\AbstractDoctrineLoader;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Binary\Loader\AbstractDoctrineLoader<extended>
 */
class AbstractDoctrineLoaderTest extends TestCase
{
    /**
     * @var MockObject|ObjectRepository|LegacyObjectRepository
     */
    private $om;

    /**
     * @var MockObject|AbstractDoctrineLoader
     */
    private $loader;

    protected function setUp(): void
    {
        if (interface_exists(LegacyObjectManager::class)) {
            $omClassName = LegacyObjectManager::class;
        } else {
            $omClassName = ObjectManager::class;
        }

        $this->om = $this
            ->getMockBuilder($omClassName)
            ->getMock();

        $this->loader = $this
            ->getMockBuilder(AbstractDoctrineLoader::class)
            ->setConstructorArgs([$this->om, \stdClass::class])
            ->getMockForAbstractClass();
    }

    public function testFindWithValidObjectFirstHit(): void
    {
        $image = new \stdClass();

        $this->loader
            ->expects($this->atLeastOnce())
            ->method('mapPathToId')
            ->with('/foo/bar')
            ->willReturn(1337);

        $this->loader
            ->expects($this->atLeastOnce())
            ->method('getStreamFromImage')
            ->with($image)
            ->willReturn(fopen('data://text/plain,foo', 'rb'));

        $this->om
            ->expects($this->atLeastOnce())
            ->method('find')
            ->with(\stdClass::class, 1337)
            ->willReturn($image);

        $this->assertSame('foo', $this->loader->find('/foo/bar'));
    }

    public function testFindWithValidObjectSecondHit(): void
    {
        $image = new \stdClass();

        $this->loader
            ->expects($this->atLeastOnce())
            ->method('mapPathToId')
            ->willReturnMap([
                ['/foo/bar.png', 1337],
                ['/foo/bar', 4711],
            ]);

        $this->loader
            ->expects($this->atLeastOnce())
            ->method('getStreamFromImage')
            ->with($image)
            ->willReturn(fopen('data://text/plain,foo', 'rb'));

        $this->om
            ->expects($this->atLeastOnce())
            ->method('find')
            ->willReturnMap([
                [\stdClass::class, 1337, null],
                [\stdClass::class, 4711, $image],
            ]);

        $this->assertSame('foo', $this->loader->find('/foo/bar.png'));
    }

    public function testFindWithInvalidObject(): void
    {
        $this->expectException(NotLoadableException::class);

        $this->loader
            ->expects($this->atLeastOnce())
            ->method('mapPathToId')
            ->with('/foo/bar')
            ->willReturn(1337);

        $this->loader
            ->expects($this->never())
            ->method('getStreamFromImage');

        $this->om
            ->expects($this->atLeastOnce())
            ->method('find')
            ->with(\stdClass::class, 1337)
            ->willReturn(null);

        $this->loader->find('/foo/bar');
    }
}
