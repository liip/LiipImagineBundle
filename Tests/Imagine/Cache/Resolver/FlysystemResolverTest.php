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
use League\Flysystem\FilesystemInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\FlysystemResolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\RequestContext;

/**
 * @covers \Liip\ImagineBundle\Imagine\Cache\Resolver\FlysystemResolver
 */
class FlysystemResolverTest extends AbstractTest
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!interface_exists(FilesystemInterface::class)) {
            $this->markTestSkipped('The league/flysystem:^1.0 PHP library is not available.');
        }
    }

    public function testImplementsResolverInterface(): void
    {
        $rc = new \ReflectionClass(FlysystemResolver::class);

        $this->assertTrue($rc->implementsInterface(ResolverInterface::class));
    }

    public function testResolveUriForFilter(): void
    {
        $resolver = new FlysystemResolver($this->createFlySystemMock(), new RequestContext(), 'http://images.example.com');

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
            ->method('deleteDir')
            ->with('media/cache/theFilter');

        $resolver = new FlysystemResolver($fs, new RequestContext(), 'http://images.example.com');
        $resolver->remove([], [$expectedFilter]);
    }

    public function testCreateObjectInAdapter(): void
    {
        $binary = new Binary('aContent', 'image/jpeg', 'jpeg');

        $fs = $this->createFlySystemMock();
        $fs
            ->expects($this->once())
            ->method('put')
            ->willReturn(true);

        $resolver = new FlysystemResolver($fs, new RequestContext(), 'http://images.example.com');

        $this->assertNull($resolver->store($binary, 'thumb/foobar.jpg', 'thumb'));
    }

    public function testIsStoredChecksObjectExistence(): void
    {
        $fs = $this->createFlySystemMock();
        $fs
            ->expects($this->once())
            ->method('has')
            ->willReturn(false);

        $resolver = new FlysystemResolver($fs, new RequestContext(), 'http://images.example.com');

        $this->assertFalse($resolver->isStored('/some-folder/path.jpg', 'thumb'));
    }

    public function testReturnResolvedImageUrlOnResolve(): void
    {
        $fs = $this->createFlySystemMock();

        $resolver = new FlysystemResolver($fs, new RequestContext(), 'http://images.example.com');

        $this->assertSame(
            'http://images.example.com/media/cache/thumb/some-folder/path.jpg',
            $resolver->resolve('/some-folder/path.jpg', 'thumb')
        );
    }

    public function testResolveWithPrefixCacheEmpty(): void
    {
        $resolver = new FlysystemResolver($this->createFlySystemMock(), new RequestContext(), 'http://images.example.com', '');

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
            ->method('has')
            ->with('media/cache/thumb/some-folder/path.jpg')
            ->willReturn(true);
        $fs
            ->expects($this->once())
            ->method('delete')
            ->with('media/cache/thumb/some-folder/path.jpg')
            ->willReturn(true);

        $resolver = new FlysystemResolver($fs, new RequestContext(), 'http://images.example.com');
        $resolver->remove(['some-folder/path.jpg'], ['thumb']);
    }

    public function testRemoveCacheForSomePathsAndFilterOnRemove(): void
    {
        $fs = $this->createFlySystemMock();
        $fs
            ->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive(
                ['media/cache/thumb/pathOne.jpg'],
                ['media/cache/thumb/pathTwo.jpg']
            )
            ->willReturn(true);
        $fs
            ->method('delete')
            ->withConsecutive(
                ['media/cache/thumb/pathOne.jpg'],
                ['media/cache/thumb/pathTwo.jpg']
            )
            ->willReturn(true);

        $resolver = new FlysystemResolver($fs, new RequestContext(), 'http://images.example.com');
        $resolver->remove(
            ['pathOne.jpg', 'pathTwo.jpg'],
            ['thumb']
        );
    }

    public function testRemoveCacheForSomePathsAndSomeFiltersOnRemove(): void
    {
        $fs = $this->createFlySystemMock();
        $fs
            ->expects($this->exactly(4))
            ->method('has')
            ->withConsecutive(
                ['media/cache/filterOne/pathOne.jpg'],
                ['media/cache/filterTwo/pathOne.jpg'],
                ['media/cache/filterOne/pathTwo.jpg'],
                ['media/cache/filterTwo/pathTwo.jpg']
            )
            ->willReturn(true);
        $fs
            ->method('delete')
            ->withConsecutive(
                ['media/cache/filterOne/pathOne.jpg'],
                ['media/cache/filterTwo/pathOne.jpg'],
                ['media/cache/filterOne/pathTwo.jpg'],
                ['media/cache/filterTwo/pathTwo.jpg']
            )
            ->willReturn(true);

        $resolver = new FlysystemResolver($fs, new RequestContext(), 'http://images.example.com');
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
            ->method('has')
            ->with('media/cache/thumb/some-folder/path.jpg')
            ->willReturn(false);
        $fs
            ->expects($this->never())
            ->method('delete');

        $resolver = new FlysystemResolver($fs, new RequestContext(), 'http://images.example.com');
        $resolver->remove(['some-folder/path.jpg'], ['thumb']);
    }

    public function testRemoveCacheForFilterOnRemove(): void
    {
        $expectedFilter = 'theFilter';

        $fs = $this->createFlySystemMock();
        $fs
            ->expects($this->once())
            ->method('deleteDir')
            ->with('media/cache/theFilter');

        $resolver = new FlysystemResolver($fs, new RequestContext(), 'http://images.example.com');
        $resolver->remove([], [$expectedFilter]);
    }

    public function testRemoveCacheForSomeFiltersOnRemove(): void
    {
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $fs = $this->createFlySystemMock();
        $fs
            ->expects($this->exactly(2))
            ->method('deleteDir')
            ->withConsecutive(
                ['media/cache/theFilterOne'],
                ['media/cache/theFilterTwo']
            );

        $resolver = new FlysystemResolver($fs, new RequestContext(), 'http://images.example.com');
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
                'deleteDir',
                'has',
                'put',
                'remove',
            ])
            ->getMock();
    }
}
