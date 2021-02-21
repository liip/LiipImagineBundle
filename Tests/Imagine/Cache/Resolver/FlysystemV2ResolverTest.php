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

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use Liip\ImagineBundle\Imagine\Cache\Resolver\FlysystemV2Resolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\RequestContext;

/**
 * @covers \Liip\ImagineBundle\Imagine\Cache\Resolver\FlysystemV2Resolver
 */
class FlysystemV2ResolverTest extends AbstractTest
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!interface_exists(FilesystemOperator::class)) {
            $this->markTestSkipped('The league/flysystem:^2.0 PHP library is not available.');
        }
    }

    public function testImplementsResolverInterface(): void
    {
        $rc = new \ReflectionClass(FlysystemV2Resolver::class);

        $this->assertTrue($rc->implementsInterface(ResolverInterface::class));
    }

    public function testResolveUriForFilter(): void
    {
        $resolver = new FlysystemV2Resolver($this->createFlySystemMock(), new RequestContext(), 'http://images.example.com');

        $this->assertSame(
            'http://images.example.com/media/cache/thumb/some-folder/path.jpg',
            $resolver->resolve('/some-folder/path.jpg', 'thumb')
        );
    }

    public function testRemoveObjectsForFilter(): void
    {
        $expectedFilter = 'theFilter';
        $fs = $this->createFlySystemMock();
        $fs
            ->expects($this->once())
            ->method('deleteDirectory')
            ->with('media/cache/theFilter');

        $resolver = new FlysystemV2Resolver($fs, new RequestContext(), 'http://images.example.com');
        $resolver->remove([], [$expectedFilter]);
    }

    public function testCreateObjectInAdapter(): void
    {
        $binary = new Binary('aContent', 'image/jpeg', 'jpeg');

        $fs = $this->createFlySystemMock();
        $fs
            ->expects($this->once())
            ->method('write');

        $resolver = new FlysystemV2Resolver($fs, new RequestContext(), 'http://images.example.com');

        $this->assertNull($resolver->store($binary, 'thumb/foobar.jpg', 'thumb'));
    }

    public function testIsStoredChecksObjectExistence(): void
    {
        $fs = $this->createFlySystemMock();
        $fs
            ->expects($this->once())
            ->method('fileExists')
            ->willReturn(false);

        $resolver = new FlysystemV2Resolver($fs, new RequestContext(), 'http://images.example.com');

        $this->assertFalse($resolver->isStored('/some-folder/path.jpg', 'thumb'));
    }

    public function testReturnResolvedImageUrlOnResolve(): void
    {
        $fs = $this->createFlySystemMock();

        $resolver = new FlysystemV2Resolver($fs, new RequestContext(), 'http://images.example.com');

        $this->assertSame(
            'http://images.example.com/media/cache/thumb/some-folder/path.jpg',
            $resolver->resolve('/some-folder/path.jpg', 'thumb')
        );
    }

    public function testResolveWithPrefixCacheEmpty(): void
    {
        $resolver = new FlysystemV2Resolver($this->createFlySystemMock(), new RequestContext(), 'http://images.example.com', '');

        $this->assertSame(
            'http://images.example.com/thumb/some-folder/path.jpg',
            $resolver->resolve('/some-folder/path.jpg', 'thumb')
        );
    }

    public function testRemoveCacheForPathAndFilterOnRemove(): void
    {
        $fs = $this->createFlySystemMock();
        $fs
            ->expects($this->once())
            ->method('fileExists')
            ->with('media/cache/thumb/some-folder/path.jpg')
            ->willReturn(true);
        $fs
            ->expects($this->once())
            ->method('delete')
            ->with('media/cache/thumb/some-folder/path.jpg');

        $resolver = new FlysystemV2Resolver($fs, new RequestContext(), 'http://images.example.com');
        $resolver->remove(['some-folder/path.jpg'], ['thumb']);
    }

    public function testRemoveCacheForSomePathsAndFilterOnRemove(): void
    {
        $fs = $this->createFlySystemMock();
        $fs
            ->expects($this->at(0))
            ->method('fileExists')
            ->with('media/cache/thumb/pathOne.jpg')
            ->willReturn(true);
        $fs
            ->expects($this->at(1))
            ->method('delete')
            ->with('media/cache/thumb/pathOne.jpg');
        $fs
            ->expects($this->at(2))
            ->method('fileExists')
            ->with('media/cache/thumb/pathTwo.jpg')
            ->willReturn(true);
        $fs
            ->expects($this->at(3))
            ->method('delete')
            ->with('media/cache/thumb/pathTwo.jpg');

        $resolver = new FlysystemV2Resolver($fs, new RequestContext(), 'http://images.example.com');
        $resolver->remove(
            ['pathOne.jpg', 'pathTwo.jpg'],
            ['thumb']
        );
    }

    public function testRemoveCacheForSomePathsAndSomeFiltersOnRemove(): void
    {
        $fs = $this->createFlySystemMock();
        $fs
            ->expects($this->at(0))
            ->method('fileExists')
            ->with('media/cache/filterOne/pathOne.jpg')
            ->willReturn(true);
        $fs
            ->expects($this->at(1))
            ->method('delete')
            ->with('media/cache/filterOne/pathOne.jpg');
        $fs
            ->expects($this->at(2))
            ->method('fileExists')
            ->with('media/cache/filterTwo/pathOne.jpg')
            ->willReturn(true);
        $fs
            ->expects($this->at(3))
            ->method('delete')
            ->with('media/cache/filterTwo/pathOne.jpg');
        $fs
            ->expects($this->at(4))
            ->method('fileExists')
            ->with('media/cache/filterOne/pathTwo.jpg')
            ->willReturn(true);
        $fs
            ->expects($this->at(5))
            ->method('delete')
            ->with('media/cache/filterOne/pathTwo.jpg');
        $fs
            ->expects($this->at(6))
            ->method('fileExists')
            ->with('media/cache/filterTwo/pathTwo.jpg')
            ->willReturn(true);
        $fs
            ->expects($this->at(7))
            ->method('delete')
            ->with('media/cache/filterTwo/pathTwo.jpg');

        $resolver = new FlysystemV2Resolver($fs, new RequestContext(), 'http://images.example.com');
        $resolver->remove(
            ['pathOne.jpg', 'pathTwo.jpg'],
            ['filterOne', 'filterTwo']
        );
    }

    public function testDoNothingWhenObjectNotExistForPathAndFilterOnRemove(): void
    {
        $fs = $this->createFlySystemMock();
        $fs
            ->expects($this->once())
            ->method('fileExists')
            ->with('media/cache/thumb/some-folder/path.jpg')
            ->willReturn(false);
        $fs
            ->expects($this->never())
            ->method('delete');

        $resolver = new FlysystemV2Resolver($fs, new RequestContext(), 'http://images.example.com');
        $resolver->remove(['some-folder/path.jpg'], ['thumb']);
    }

    public function testRemoveCacheForFilterOnRemove(): void
    {
        $expectedFilter = 'theFilter';

        $fs = $this->createFlySystemMock();
        $fs
            ->expects($this->once())
            ->method('deleteDirectory')
            ->with('media/cache/theFilter');

        $resolver = new FlysystemV2Resolver($fs, new RequestContext(), 'http://images.example.com');
        $resolver->remove([], [$expectedFilter]);
    }

    public function testRemoveCacheForSomeFiltersOnRemove(): void
    {
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $fs = $this->createFlySystemMock();
        $fs
            ->expects($this->at(0))
            ->method('deleteDirectory')
            ->with('media/cache/theFilterOne');
        $fs
            ->expects($this->at(1))
            ->method('deleteDirectory')
            ->with('media/cache/theFilterTwo');

        $resolver = new FlysystemV2Resolver($fs, new RequestContext(), 'http://images.example.com');
        $resolver->remove([], [$expectedFilterOne, $expectedFilterTwo]);
    }

    /**
     * @return MockObject|Filesystem
     */
    protected function createFlySystemMock()
    {
        return $this
            ->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'delete',
                'deleteDirectory',
                'fileExists',
                'write',
            ])
            ->getMock();
    }
}
