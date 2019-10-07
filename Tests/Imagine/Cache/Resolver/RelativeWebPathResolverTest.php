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

use Liip\ImagineBundle\Imagine\Cache\Resolver\RelativeWebPathResolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Utility\Path\PathResolver;
use Liip\ImagineBundle\Utility\Path\PathResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @covers \Liip\ImagineBundle\Imagine\Cache\Resolver\RelativeWebPathResolver
 */
class RelativeWebPathResolverTest extends TestCase
{
    /**
     * @var MockObject|Filesystem
     */
    private $filesystem;
    /**
     * @var MockObject|PathResolverInterface
     */
    private $pathResolverUtil;
    /**
     * @var RelativeWebPathResolver
     */
    private $relativeWebPathResolver;
    /**
     * @var string
     */
    private $basePath;

    public function setUp()
    {
        $this->filesystem = $this->getMockBuilder(Filesystem::class)->getMock();
        $this->pathResolverUtil = $this->getMockBuilder(PathResolverInterface::class)->getMock();
        $this->relativeWebPathResolver = new RelativeWebPathResolver($this->filesystem, $this->pathResolverUtil);

        $this->basePath = sys_get_temp_dir().'/aWebRoot';
    }

    public function testImplementsResolverInterface(): void
    {
        $this->assertInstanceOf(ResolverInterface::class, $this->relativeWebPathResolver);
    }

    public function testResolve(): void
    {
        $path = 'aPath';
        $filter = 'aFilter';
        $fileUrl = 'cacheDir/aFilter/aPath';

        $this->pathResolverUtil
            ->expects($this->once())
            ->method('getFileUrl')
            ->with($this->equalTo($path), $this->equalTo($filter))
            ->willReturn($fileUrl);

        $actualFileUrl = $this->relativeWebPathResolver->resolve($path, $filter);

        $this->assertSame(sprintf('/%s', $fileUrl), $actualFileUrl);
    }

    public function testOnSameConstructorArguments(): void
    {
        $this->assertAttributeSame($this->filesystem, 'filesystem', $this->relativeWebPathResolver);
        $this->assertAttributeSame($this->pathResolverUtil, 'pathResolver', $this->relativeWebPathResolver);
    }

    public function testFileIsStored(): void
    {
        $existingFile = $this->basePath.'/aCachePrefix/aFilter/existingPath';
        $filesystem = new Filesystem();
        $filesystem->mkdir(dirname($existingFile));
        $filesystem->touch($existingFile);

        $pathResolver = new PathResolver($this->basePath, 'aCachePrefix');
        $resolver = new RelativeWebPathResolver(
            $this->filesystem,
            $pathResolver
        );

        $this->assertTrue($resolver->isStored('existingPath', 'aFilter'));
        $filesystem->remove($this->basePath);
    }

    public function testFileIsNotStored(): void
    {
        $existingFile = $this->basePath.'/aCachePrefix/aFilter/existingPath';
        $filesystem = new Filesystem();
        $filesystem->mkdir(dirname($existingFile));
        $filesystem->touch($existingFile);

        $pathResolver = new PathResolver($this->basePath, 'aCachePrefix');
        $resolver = new RelativeWebPathResolver(
            $this->filesystem,
            $pathResolver
        );

        $this->assertFalse($resolver->isStored('notExisting file', 'aFilter'));
        $filesystem->remove($this->basePath);
    }

    public function testStore(): void
    {
        $path = 'aPath';
        $filter = 'aFilter';
        $filePath = '/rootDir/cacheDir/file';
        $fileContent = 'theFileContent';

        $binary = new Binary($fileContent, 'applivation/customFile', 'custom');

        $this->pathResolverUtil
            ->expects($this->once())
            ->method('getFilePath')
            ->with($this->equalTo($path), $this->equalTo($filter))
            ->willReturn($filePath);

        $this->filesystem
            ->expects($this->once())
            ->method('dumpFile')
            ->with($this->equalTo($filePath), $this->equalTo($fileContent));

        $this->relativeWebPathResolver->store($binary, $path, $filter);
    }

    public function testRemoveWithEmptyInputArrays(): void
    {
        $this->filesystem
            ->expects($this->exactly(0))
            ->method('remove');

        $this->relativeWebPathResolver->remove([], []);
    }

    public function testRemoveWithEmptyPathsArrayAndSingleFilter(): void
    {
        $filter = 'aFilter';
        $cacheRoot = '/root/cacheFolder';

        $this->pathResolverUtil
            ->expects($this->once())
            ->method('getCacheRoot')
            ->willReturn($cacheRoot);

        $this->filesystem
            ->expects($this->once())
            ->method('remove')
            ->with(
                $this->equalTo(
                    [
                        sprintf('%s/%s', $cacheRoot, $filter),
                    ]
                )
            );

        $this->relativeWebPathResolver->remove([], [$filter]);
    }

    public function testRemoveWithEmptyPathsArrayAndMultipleFilters(): void
    {
        $filterOne = 'aFilterOne';
        $filterTwo = 'aFilterTwo';
        $cacheRoot = '/root/cacheFolder';

        $this->pathResolverUtil
            ->expects($this->exactly(2))
            ->method('getCacheRoot')
            ->willReturn($cacheRoot);

        $this->filesystem
            ->expects($this->once())
            ->method('remove')
            ->with(
                $this->equalTo(
                    [
                        sprintf('%s/%s', $cacheRoot, $filterOne),
                        sprintf('%s/%s', $cacheRoot, $filterTwo),
                    ]
                )
            );

        $this->relativeWebPathResolver->remove([], [$filterOne, $filterTwo]);
    }

    public function testRemoveWithMultiplePathaAndFilters(): void
    {
        $filterOne = 'aFilterOne';
        $filterTwo = 'aFilterTwo';
        $pathOne = 'aPathOne';
        $pathTwo = 'aPathTwo';
        $cacheRoot = '/root/cacheFolder';

        $this->pathResolverUtil
            ->expects($this->exactly(0))
            ->method('getCacheRoot');

        $this->pathResolverUtil
            ->method('getFilePath')
            ->willReturnMap(
                [
                    [$pathOne, $filterOne, sprintf('%s/%s/%s', $cacheRoot, $filterOne, $pathOne)],
                    [$pathOne, $filterTwo, sprintf('%s/%s/%s', $cacheRoot, $filterTwo, $pathOne)],
                    [$pathTwo, $filterOne, sprintf('%s/%s/%s', $cacheRoot, $filterOne, $pathTwo)],
                    [$pathTwo, $filterTwo, sprintf('%s/%s/%s', $cacheRoot, $filterTwo, $pathTwo)],
                ]
            );

        $this->filesystem
            ->expects($this->at(0))
            ->method('remove')
            ->with(sprintf('%s/%s/%s', $cacheRoot, $filterOne, $pathOne));
        $this->filesystem
            ->expects($this->at(1))
            ->method('remove')
            ->with(sprintf('%s/%s/%s', $cacheRoot, $filterTwo, $pathOne));
        $this->filesystem
            ->expects($this->at(2))
            ->method('remove')
            ->with(sprintf('%s/%s/%s', $cacheRoot, $filterOne, $pathTwo));
        $this->filesystem
            ->expects($this->at(3))
            ->method('remove')
            ->with(sprintf('%s/%s/%s', $cacheRoot, $filterTwo, $pathTwo));

        $this->relativeWebPathResolver->remove(
            [$pathOne, $pathTwo],
            [$filterOne, $filterTwo]
        );
    }
}
