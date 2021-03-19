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
use PHPUnit\Framework\MockObject\MockObject;
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

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->basePath = sys_get_temp_dir().'/aWebRoot';
        $this->existingFile = $this->basePath.'/aCachePrefix/aFilter/existingPath';
        $this->filesystem->mkdir(\dirname($this->existingFile));
        $this->filesystem->touch($this->existingFile);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->basePath);
    }

    public function testImplementsResolverInterface(): void
    {
        $rc = new \ReflectionClass(WebPathResolver::class);

        $this->assertTrue($rc->implementsInterface(ResolverInterface::class));
    }

    public function testCouldBeConstructedWithRequiredArguments(): void
    {
        $filesystemMock = $this->createFilesystemMock();
        $requestContext = new RequestContext();
        $webRoot = 'theWebRoot';

        $resolver = new WebPathResolver($filesystemMock, $requestContext, $webRoot);

        $this->assertAttributeSame($filesystemMock, 'filesystem', $resolver);
        $this->assertAttributeSame($requestContext, 'requestContext', $resolver);
        $this->assertAttributeSame($webRoot, 'webRoot', $resolver);
    }

    public function testCouldBeConstructedWithOptionalArguments(): void
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext(),
            'aWebRoot',
            'theCachePrefix'
        );

        $this->assertAttributeSame('theCachePrefix', 'cachePrefix', $resolver);
    }

    public function testTrimRightSlashFromWebPathOnConstruct(): void
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext(),
            'aWebRoot/',
            'theCachePrefix'
        );

        $this->assertAttributeSame('aWebRoot', 'webRoot', $resolver);
    }

    public function testRemoveDoubleSlashFromWebRootOnConstruct(): void
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext(),
            'aWeb//Root',
            '/aCachePrefix'
        );

        $this->assertAttributeSame('aWeb/Root', 'webRoot', $resolver);
    }

    public function testTrimRightSlashFromCachePrefixOnConstruct(): void
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext(),
            'aWebRoot',
            '/aCachePrefix'
        );

        $this->assertAttributeSame('aCachePrefix', 'cachePrefix', $resolver);
    }

    public function testRemoveDoubleSlashFromCachePrefixOnConstruct(): void
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext(),
            'aWebRoot',
            'aCache//Prefix'
        );

        $this->assertAttributeSame('aCache/Prefix', 'cachePrefix', $resolver);
    }

    public function testReturnTrueIfFileExistsOnIsStored(): void
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext(),
            $this->basePath,
            'aCachePrefix'
        );

        $this->assertTrue($resolver->isStored('existingPath', 'aFilter'));
    }

    public function testReturnFalseIfFileNotExistsOnIsStored(): void
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext(),
            $this->basePath,
            'aCachePrefix'
        );

        $this->assertFalse($resolver->isStored('nonExistingPath', 'aFilter'));
    }

    public function testReturnFalseIfIsNotFile(): void
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext(),
            $this->basePath,
            'aCachePrefix'
        );

        $this->assertFalse($resolver->isStored('', 'aFilter'));
    }

    public function testComposeSchemaHostAndFileUrlOnResolve(): void
    {
        $requestContext = new RequestContext();
        $requestContext->setScheme('theSchema');
        $requestContext->setHost('thehost');

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $requestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertSame(
            'theschema://thehost/aCachePrefix/aFilter/aPath',
            $resolver->resolve('aPath', 'aFilter')
        );
    }

    public function testComposeSchemaHostAndBasePathWithPhpFileAndFileUrlOnResolve(): void
    {
        $requestContext = new RequestContext();
        $requestContext->setScheme('theSchema');
        $requestContext->setHost('thehost');
        $requestContext->setBaseUrl('/theBasePath/app.php');

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $requestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertSame(
            'theschema://thehost/theBasePath/aCachePrefix/aFilter/aPath',
            $resolver->resolve('aPath', 'aFilter')
        );
    }

    public function testResolveWithPrefixCacheEmpty(): void
    {
        $requestContext = new RequestContext();
        $requestContext->setScheme('theSchema');
        $requestContext->setHost('thehost');
        $requestContext->setBaseUrl('/theBasePath/app.php');

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $requestContext,
            '/aWebRoot',
            ''
        );

        $this->assertSame(
            'theschema://thehost/theBasePath/aFilter/aPath',
            $resolver->resolve('aPath', 'aFilter')
        );
    }

    public function testComposeSchemaHostAndBasePathWithDirsOnlyAndFileUrlOnResolve(): void
    {
        $requestContext = new RequestContext();
        $requestContext->setScheme('theSchema');
        $requestContext->setHost('thehost');
        $requestContext->setBaseUrl('/theBasePath/theSubBasePath');

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $requestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertSame(
            'theschema://thehost/theBasePath/theSubBasePath/aCachePrefix/aFilter/aPath',
            $resolver->resolve('aPath', 'aFilter')
        );
    }

    public function testComposeSchemaHostAndBasePathWithBackSplashOnResolve(): void
    {
        $requestContext = new RequestContext();
        $requestContext->setScheme('theSchema');
        $requestContext->setHost('thehost');
        $requestContext->setBaseUrl('\\');

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $requestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertSame(
            'theschema://thehost/aCachePrefix/aFilter/aPath',
            $resolver->resolve('aPath', 'aFilter')
        );
    }

    public function testComposeSchemaHttpAndCustomPortAndFileUrlOnResolve(): void
    {
        $requestContext = new RequestContext();
        $requestContext->setScheme('http');
        $requestContext->setHost('thehost');
        $requestContext->setHttpPort(88);

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $requestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertSame(
            'http://thehost:88/aCachePrefix/aFilter/aPath',
            $resolver->resolve('aPath', 'aFilter')
        );
    }

    public function testComposeSchemaHttpsAndCustomPortAndFileUrlOnResolve(): void
    {
        $requestContext = new RequestContext();
        $requestContext->setScheme('https');
        $requestContext->setHost('thehost');
        $requestContext->setHttpsPort(444);

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $requestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertSame(
            'https://thehost:444/aCachePrefix/aFilter/aPath',
            $resolver->resolve('aPath', 'aFilter')
        );
    }

    public function testDumpBinaryContentOnStore(): void
    {
        $binary = new Binary('theContent', 'aMimeType', 'aFormat');

        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->once())
            ->method('dumpFile')
            ->with('/aWebRoot/aCachePrefix/aFilter/aPath', 'theContent');

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext(),
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertNull($resolver->store($binary, 'aPath', 'aFilter'));
    }

    public function testDoNothingIfFiltersAndPathsEmptyOnRemove(): void
    {
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->never())
            ->method('remove');

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext(),
            '/aWebRoot',
            'aCachePrefix'
        );

        $resolver->remove([], []);
    }

    public function testRemoveCacheForPathAndFilterOnRemove(): void
    {
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->once())
            ->method('remove')
            ->with('/aWebRoot/aCachePrefix/aFilter/aPath');

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext(),
            '/aWebRoot',
            'aCachePrefix'
        );

        $resolver->remove(['aPath'], ['aFilter']);
    }

    public function testRemoveCacheForSomePathsAndFilterOnRemove(): void
    {
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->at(0))
            ->method('remove')
            ->with('/aWebRoot/aCachePrefix/aFilter/aPathOne');
        $filesystemMock
            ->expects($this->at(1))
            ->method('remove')
            ->with('/aWebRoot/aCachePrefix/aFilter/aPathTwo');

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext(),
            '/aWebRoot',
            'aCachePrefix'
        );

        $resolver->remove(['aPathOne', 'aPathTwo'], ['aFilter']);
    }

    public function testRemoveCacheForSomePathsAndSomeFiltersOnRemove(): void
    {
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->at(0))
            ->method('remove')
            ->with('/aWebRoot/aCachePrefix/aFilterOne/aPathOne');
        $filesystemMock
            ->expects($this->at(1))
            ->method('remove')
            ->with('/aWebRoot/aCachePrefix/aFilterTwo/aPathOne');
        $filesystemMock
            ->expects($this->at(2))
            ->method('remove')
            ->with('/aWebRoot/aCachePrefix/aFilterOne/aPathTwo');
        $filesystemMock
            ->expects($this->at(3))
            ->method('remove')
            ->with('/aWebRoot/aCachePrefix/aFilterTwo/aPathTwo');

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext(),
            '/aWebRoot',
            'aCachePrefix'
        );

        $resolver->remove(
            ['aPathOne', 'aPathTwo'],
            ['aFilterOne', 'aFilterTwo']
        );
    }

    public function testRemoveCacheForFilterOnRemove(): void
    {
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->once())
            ->method('remove')
            ->with([
                '/aWebRoot/aCachePrefix/aFilter',
            ]);

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext(),
            '/aWebRoot',
            'aCachePrefix'
        );

        $resolver->remove([], ['aFilter']);
    }

    public function testRemoveCacheForSomeFiltersOnRemove(): void
    {
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->once())
            ->method('remove')
            ->with([
                '/aWebRoot/aCachePrefix/aFilterOne',
                '/aWebRoot/aCachePrefix/aFilterTwo',
            ]);

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext(),
            '/aWebRoot',
            'aCachePrefix'
        );

        $resolver->remove([], ['aFilterOne', 'aFilterTwo']);
    }

    public function testShouldRemoveDoubleSlashInUrl(): void
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext(),
            '/aWebRoot',
            'aCachePrefix'
        );

        $rc = new \ReflectionClass($resolver);
        $method = $rc->getMethod('getFileUrl');
        $method->setAccessible(true);

        $result = $method->invokeArgs($resolver, ['/cats.jpg', 'some_filter']);

        $this->assertSame('aCachePrefix/some_filter/cats.jpg', $result);
    }

    public function testShouldSanitizeSeparatorBetweenSchemeAndAuthorityInUrl(): void
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext(),
            '/aWebRoot',
            'aCachePrefix'
        );

        $rc = new \ReflectionClass($resolver);
        $method = $rc->getMethod('getFileUrl');
        $method->setAccessible(true);

        $result = $method->invokeArgs($resolver, ['https://some.meme.com/cute/cats.jpg', 'some_filter']);

        $this->assertSame('aCachePrefix/some_filter/https---some.meme.com/cute/cats.jpg', $result);
    }

    /**
     * Method was added because it is deprecated in PHPUnit 8
     */
    public static function assertAttributeSame($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
    {
        $reflector = new \ReflectionObject($actualClassOrObject);
        $attribute = $reflector->getProperty($actualAttributeName);
        $attribute->setAccessible(true);
        $actual = $attribute->getValue($actualClassOrObject);
        $attribute->setAccessible(false);

        self::assertSame($expected, $actual, $message);
    }

    /**
     * @return MockObject|Filesystem
     */
    protected function createFilesystemMock()
    {
        return $this->getMockBuilder(Filesystem::class)->getMock();
    }
}
