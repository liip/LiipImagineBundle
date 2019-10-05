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

use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Utility\Path\PathResolver;
use Liip\ImagineBundle\Utility\Path\PathResolverInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RequestContext;

/**
 * @covers \Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver
 */
class WebPathResolverTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $basePath;

    /**
     * @var string
     */
    private $existingFile;

    public function setUp()
    {
        $this->filesystem = new Filesystem();
        $this->basePath = sys_get_temp_dir().'/aWebRoot';
        $this->existingFile = $this->basePath.'/aCachePrefix/aFilter/existingPath';
        $this->filesystem->mkdir(\dirname($this->existingFile));
        $this->filesystem->touch($this->existingFile);
    }

    public function tearDown()
    {
        $this->filesystem->remove($this->basePath);
    }

    public function testImplementsResolverInterface()
    {
        $rc = new \ReflectionClass(WebPathResolver::class);

        $this->assertTrue($rc->implementsInterface(ResolverInterface::class));
    }

    public function testCouldBeConstructedWithRequiredArguments()
    {
        $filesystemMock = $this->createFilesystemMock();
        $pathResolver = $this->createPathResolverMock();
        $requestContext = new RequestContext();

        $resolver = new WebPathResolver($filesystemMock, $pathResolver, $requestContext);

        $this->assertAttributeSame($filesystemMock, 'filesystem', $resolver);
        $this->assertAttributeSame($pathResolver, 'pathResolver', $resolver);
        $this->assertAttributeSame($requestContext, 'requestContext', $resolver);
    }

    public function testReturnTrueIfFileExistsOnIsStored()
    {
        $pathResolver = new PathResolver($this->basePath, 'aCachePrefix');
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $pathResolver,
            new RequestContext()
        );

        $this->assertTrue($resolver->isStored('existingPath', 'aFilter'));
    }

    public function testReturnFalseIfFileNotExistsOnIsStored()
    {
        $pathResolver = new PathResolver($this->basePath, 'aCachePrefix');
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $pathResolver,
            new RequestContext()
        );

        $this->assertFalse($resolver->isStored('nonExistingPath', 'aFilter'));
    }

    public function testReturnFalseIfIsNotFile()
    {
        $pathResolver = new PathResolver($this->basePath, 'aCachePrefix');
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $pathResolver,
            new RequestContext()
        );

        $this->assertFalse($resolver->isStored('', 'aFilter'));
    }

    public function testComposeSchemaHostAndFileUrlOnResolve()
    {
        $path = 'aPath';
        $filter = 'aFilter';

        $requestContext = new RequestContext();
        $requestContext->setScheme('theSchema');
        $requestContext->setHost('thehost');

        $pathResolver = $this->createPathResolverMock();
        $pathResolver
            ->method('getFileUrl')
            ->with(
                $this->equalTo($path),
                $this->equalTo($filter)
            )
            ->willReturn(
                'aCachePrefix/aFilter/aPath'
            );

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(), $pathResolver, $requestContext
        );

        $this->assertSame(
            'theschema://thehost/aCachePrefix/aFilter/aPath',
            $resolver->resolve($path, $filter)
        );
    }

    public function testComposeSchemaHostAndBasePathWithPhpFileAndFileUrlOnResolve()
    {
        $path = 'aPath';
        $filter = 'aFilter';

        $requestContext = new RequestContext();
        $requestContext->setScheme('theSchema');
        $requestContext->setHost('thehost');
        $requestContext->setBaseUrl('/theBasePath/app.php');

        $pathResolver = $this->createPathResolverMock();
        $pathResolver->method('getFileUrl')
            ->with(
                $this->equalTo($path),
                $this->equalTo($filter)
            )
            ->willReturn(
                'aCachePrefix/aFilter/aPath'
            );

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(), $pathResolver, $requestContext
        );

        $this->assertSame(
            'theschema://thehost/theBasePath/aCachePrefix/aFilter/aPath',
            $resolver->resolve($path, $filter)
        );
    }

    public function testResolveWithPrefixCacheEmpty()
    {
        $requestContext = new RequestContext();
        $requestContext->setScheme('theSchema');
        $requestContext->setHost('thehost');
        $requestContext->setBaseUrl('/theBasePath/app.php');

        $pathResolver = new PathResolver('/aWebRoot', '');

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $pathResolver,
            $requestContext
        );

        $this->assertSame(
            'theschema://thehost/theBasePath/aFilter/aPath',
            $resolver->resolve('aPath', 'aFilter')
        );
    }

    public function testComposeSchemaHostAndBasePathWithDirsOnlyAndFileUrlOnResolve()
    {
        $path = 'aPath';
        $filter = 'aFilter';

        $requestContext = new RequestContext();
        $requestContext->setScheme('theSchema');
        $requestContext->setHost('thehost');
        $requestContext->setBaseUrl('/theBasePath/theSubBasePath');

        $pathResolver = $this->createPathResolverMock();
        $pathResolver->method('getFileUrl')
            ->with(
                $this->equalTo($path),
                $this->equalTo($filter)
            )
            ->willReturn(
                'aCachePrefix/aFilter/aPath'
            );

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(), $pathResolver, $requestContext
        );

        $this->assertSame(
            'theschema://thehost/theBasePath/theSubBasePath/aCachePrefix/aFilter/aPath',
            $resolver->resolve($path, $filter)
        );
    }

    public function testComposeSchemaHostAndBasePathWithBackSplashOnResolve()
    {
        $path = 'aPath';
        $filter = 'aFilter';
        $fileUrl = 'aCachePrefix/aFilter/aPath';

        $requestContext = new RequestContext();
        $requestContext->setScheme('theSchema');
        $requestContext->setHost('thehost');
        $requestContext->setBaseUrl('\\');

        $pathResolver = $this->createPathResolverMock();
        $pathResolver->method('getFileUrl')
            ->with(
                $this->equalTo($path),
                $this->equalTo($filter)
            )
            ->willReturn($fileUrl);

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $pathResolver,
            $requestContext
        );

        $this->assertSame(
            sprintf('theschema://thehost/%s', $fileUrl),
            $resolver->resolve($path, $filter)
        );
    }

    public function testComposeSchemaHttpAndCustomPortAndFileUrlOnResolve()
    {
        $path = 'aPath';
        $filter = 'aFilter';
        $fileUrl = 'aCachePrefix/aFilter/aPath';

        $requestContext = new RequestContext();
        $requestContext->setScheme('http');
        $requestContext->setHost('thehost');
        $requestContext->setHttpPort(88);

        $pathResolver = $this->createPathResolverMock();
        $pathResolver->method('getFileUrl')
            ->with(
                $this->equalTo($path),
                $this->equalTo($filter)
            )
            ->willReturn($fileUrl);

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $pathResolver,
            $requestContext
        );

        $this->assertSame(
            sprintf('http://thehost:88/%s', $fileUrl),
            $resolver->resolve($path, $filter)
        );
    }

    public function testComposeSchemaHttpsAndCustomPortAndFileUrlOnResolve()
    {
        $path = 'aPath';
        $filter = 'aFilter';
        $fileUrl = 'aCachePrefix/aFilter/aPath';

        $requestContext = new RequestContext();
        $requestContext->setScheme('https');
        $requestContext->setHost('thehost');
        $requestContext->setHttpsPort(444);

        $pathResolver = $this->createPathResolverMock();
        $pathResolver->method('getFileUrl')
            ->with(
                $this->equalTo($path),
                $this->equalTo($filter)
            )
            ->willReturn($fileUrl);

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $pathResolver,
            $requestContext
        );

        $this->assertSame(
            sprintf('https://thehost:444/%s', $fileUrl),
            $resolver->resolve($path, $filter)
        );
    }

    public function testDumpBinaryContentOnStore()
    {
        $path = 'aPath';
        $filter = 'aFilter';
        $fileUrl = '/aWebRoot/aCachePrefix/aFilter/aPath';
        $fileContent = 'theContent';

        $binary = new Binary($fileContent, 'aMimeType', 'aFormat');

        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->once())
            ->method('dumpFile')
            ->with(
                $this->equalTo($fileUrl),
                $this->equalTo($fileContent)
            );

        $pathResolver = $this->createPathResolverMock();
        $pathResolver->method('getFilePath')
            ->with(
                $this->equalTo($path),
                $this->equalTo($filter)
            )
            ->willReturn(
                $fileUrl
            );

        $resolver = new WebPathResolver(
            $filesystemMock,
            $pathResolver,
            new RequestContext()
        );

        $this->assertNull($resolver->store($binary, $path, $filter));
    }

    public function testDoNothingIfFiltersAndPathsEmptyOnRemove()
    {
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock->expects($this->never())->method('remove');

        $resolver = new WebPathResolver(
            $filesystemMock,
            $this->createPathResolverMock(),
            new RequestContext()
        );

        $resolver->remove([], []);
    }

    public function testRemoveCacheForPathAndFilterOnRemove()
    {
        $filePath = '/aWebRoot/aCachePrefix/aFilter/aPath';
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock->expects($this->once())->method('remove')->with($filePath);

        $pathResolver = $this->createPathResolverMock();
        $pathResolver->method('getFilePath')->willReturn($filePath);

        $resolver = new WebPathResolver(
            $filesystemMock,
            $pathResolver,
            new RequestContext()
        );

        $resolver->remove(['aPath'], ['aFilter']);
    }

    public function testRemoveCacheForSomePathsAndFilterOnRemove()
    {
        $cacheRoot = '/aWebRoot/aCachePrefix';
        $pathOne = 'aPathOne';
        $pathTwo = 'aPathTwo';
        $filter = 'aFilter';

        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->at(0))
            ->method('remove')
            ->with(
                sprintf('%s/%s/%s', $cacheRoot, $filter, $pathOne)
            );
        $filesystemMock
            ->expects($this->at(1))
            ->method('remove')
            ->with(
                sprintf('%s/%s/%s', $cacheRoot, $filter, $pathTwo)
            );

        $pathResolver = $this->createPathResolverMock();
        $pathResolver->method('getFilePath')
            ->willReturnMap(
                [
                    [$pathOne, $filter, sprintf('%s/%s/%s', $cacheRoot, $filter, $pathOne)],
                    [$pathTwo, $filter, sprintf('%s/%s/%s', $cacheRoot, $filter, $pathTwo)],
                ]
            );

        $resolver = new WebPathResolver(
            $filesystemMock,
            $pathResolver,
            new RequestContext()
        );

        $resolver->remove([$pathOne, $pathTwo], [$filter]);
    }

    public function testRemoveCacheForSomePathsAndSomeFiltersOnRemove()
    {
        $cacheRoot = '/aWebRoot/aCachePrefix';
        $pathOne = 'aPathOne';
        $pathTwo = 'aPathTwo';
        $filterOne = 'aFilterOne';
        $filterTwo = 'aFilterTwo';

        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->at(0))
            ->method('remove')
            ->with(
                sprintf('%s/%s/%s', $cacheRoot, $filterOne, $pathOne)
            );
        $filesystemMock
            ->expects($this->at(1))
            ->method('remove')
            ->with(
                sprintf('%s/%s/%s', $cacheRoot, $filterTwo, $pathOne)
            );
        $filesystemMock
            ->expects($this->at(2))
            ->method('remove')
            ->with(
                sprintf('%s/%s/%s', $cacheRoot, $filterOne, $pathTwo)
            );
        $filesystemMock
            ->expects($this->at(3))
            ->method('remove')
            ->with(
                sprintf('%s/%s/%s', $cacheRoot, $filterTwo, $pathTwo)
            );

        $pathResolver = $this->createPathResolverMock();
        $pathResolver
            ->method('getFilePath')
            ->willReturnMap(
                [
                    [$pathOne, $filterOne, sprintf('%s/%s/%s', $cacheRoot, $filterOne, $pathOne)],
                    [$pathOne, $filterTwo, sprintf('%s/%s/%s', $cacheRoot, $filterTwo, $pathOne)],
                    [$pathTwo, $filterOne, sprintf('%s/%s/%s', $cacheRoot, $filterOne, $pathTwo)],
                    [$pathTwo, $filterTwo, sprintf('%s/%s/%s', $cacheRoot, $filterTwo, $pathTwo)],
                ]
            );

        $resolver = new WebPathResolver(
            $filesystemMock,
            $pathResolver,
            new RequestContext()
        );

        $resolver->remove(
            [$pathOne, $pathTwo],
            [$filterOne, $filterTwo]
        );
    }

    public function testRemoveCacheForFilterOnRemove()
    {
        $cacheRoot = '/aWebRoot/aCachePrefix';

        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->once())
            ->method('remove')
            ->with(
                [
                    sprintf('%s/aFilter', $cacheRoot),
                ]
            );

        $pathResolver = $this->createPathResolverMock();
        $pathResolver
            ->method('getCacheRoot')
            ->willReturn($cacheRoot);

        $resolver = new WebPathResolver(
            $filesystemMock,
            $pathResolver,
            new RequestContext()
        );

        $resolver->remove([], ['aFilter']);
    }

    public function testRemoveCacheForSomeFiltersOnRemove()
    {
        $cacheRoot = '/aWebRoot/aCachePrefix';

        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->once())
            ->method('remove')
            ->with(
                [
                    sprintf('%s/aFilterOne', $cacheRoot),
                    sprintf('%s/aFilterTwo', $cacheRoot),
                ]
            );

        $pathResolver = $this->createPathResolverMock();
        $pathResolver
            ->method('getCacheRoot')
            ->willReturn($cacheRoot);

        $resolver = new WebPathResolver(
            $filesystemMock,
            $pathResolver,
            new RequestContext()
        );

        $resolver->remove([], ['aFilterOne', 'aFilterTwo']);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|PathResolverInterface
     */
    public function createPathResolverMock()
    {
        return $this->getMockBuilder(PathResolverInterface::class)->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Filesystem
     */
    protected function createFilesystemMock()
    {
        return $this->getMockBuilder(Filesystem::class)->getMock();
    }
}
