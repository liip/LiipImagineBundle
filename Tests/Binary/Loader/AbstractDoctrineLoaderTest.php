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

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Liip\ImagineBundle\Binary\Loader\AbstractDoctrineLoader;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Binary\Loader\AbstractDoctrineLoader<extended>
 */
class AbstractDoctrineLoaderTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ObjectRepository
     */
    private $om;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AbstractDoctrineLoader
     */
    private $loader;

    public function setUp()
    {
        if (!interface_exists(ObjectManager::class)) {
            $this->markTestSkipped('Requires the doctrine/orm package.');
        }

        $this->om = $this
            ->getMockBuilder(ObjectManager::class)
            ->getMock();

        $this->loader = $this
            ->getMockBuilder(AbstractDoctrineLoader::class)
            ->setConstructorArgs([$this->om])
            ->getMockForAbstractClass();
    }

    public function testFindWithValidObjectFirstHit()
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
            ->with(null, 1337)
            ->willReturn($image);

        $this->assertSame('foo', $this->loader->find('/foo/bar'));
    }

    public function testFindWithValidObjectSecondHit()
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
                [null, 1337, null],
                [null, 4711, $image],
            ]);

        $this->assertSame('foo', $this->loader->find('/foo/bar.png'));
    }

    public function testFindWithInvalidObject()
    {
        $this->expectException(\Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException::class);

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
            ->with(null, 1337)
            ->willReturn(null);

        $this->loader->find('/foo/bar');
    }
}
