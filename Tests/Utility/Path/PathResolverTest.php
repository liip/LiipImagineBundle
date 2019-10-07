<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Utility\Path;

use Liip\ImagineBundle\Utility\Path\PathResolver;
use Liip\ImagineBundle\Utility\Path\PathResolverInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Utility\Path\PathResolver
 */
class PathResolverTest extends TestCase
{
    public function testForInterfaceImplementation(): void
    {
        $this->assertTrue(is_a(PathResolver::class, PathResolverInterface::class, true));
    }

    public function testPropertiesForSameConstructorArguments(): void
    {
        $webRootDir = 'aWebRootDir';
        $cachePrefix = 'aCachePrefix';
        $pathResolver = new PathResolver($webRootDir, $cachePrefix);
        $this->assertAttributeSame($webRootDir, 'webRoot', $pathResolver);
        $this->assertAttributeSame($cachePrefix, 'cachePrefix', $pathResolver);
    }

    public function testWebRootPathNormalizer(): void
    {
        $pathResolver = new PathResolver('aWebRootDir/');
        $this->assertAttributeSame('aWebRootDir', 'webRoot', $pathResolver);
    }

    public function testCachePrefixNormalizer(): void
    {
        $pathResolver = new PathResolver('aWebRootDir', '/cachePrefix');
        $this->assertAttributeSame('cachePrefix', 'cachePrefix', $pathResolver);
    }

    public function testForDoubleSlashReplacing(): void
    {
        $pathResolver = new PathResolver(
            'aWebRootDir//subRootDir',
            'cachePrefix//subCacheDir'
        );
        $this->assertAttributeSame(
            'aWebRootDir/subRootDir',
            'webRoot',
            $pathResolver
        );
        $this->assertAttributeSame(
            'cachePrefix/subCacheDir',
            'cachePrefix',
            $pathResolver
        );
    }

    public function testPathsNormalizerWithSubfolders(): void
    {
        $pathResolver = new PathResolver(
            'aWebRootDir/subRootDir',
            'cachePrefix/subCacheDir/anotherSubCacheDir'
        );
        $this->assertAttributeSame(
            'aWebRootDir/subRootDir',
            'webRoot',
            $pathResolver
        );
        $this->assertAttributeSame(
            'cachePrefix/subCacheDir/anotherSubCacheDir',
            'cachePrefix',
            $pathResolver
        );
    }

    public function testCacheRootPathDirCreationWithoutInvalidSlashes(): void
    {
        $pathResolver = new PathResolver(
            'aWebRootDir/subRootDir',
            'cachePrefix/subCacheDir'
        );
        $this->assertAttributeSame(
            'aWebRootDir/subRootDir',
            'webRoot',
            $pathResolver
        );
        $this->assertAttributeSame(
            'cachePrefix/subCacheDir',
            'cachePrefix',
            $pathResolver
        );
        $this->assertAttributeSame(
            'aWebRootDir/subRootDir/cachePrefix/subCacheDir',
            'cacheRoot',
            $pathResolver
        );
    }

    public function testCacheRootPathDirCreationWithInvalidSlashes(): void
    {
        $pathResolver = new PathResolver(
            'aWebRootDir/subRootDir/',
            '/cachePrefix/subCacheDir'
        );
        $this->assertAttributeSame(
            'aWebRootDir/subRootDir',
            'webRoot',
            $pathResolver
        );
        $this->assertAttributeSame(
            'cachePrefix/subCacheDir',
            'cachePrefix',
            $pathResolver
        );
        $this->assertAttributeSame(
            'aWebRootDir/subRootDir/cachePrefix/subCacheDir',
            'cacheRoot',
            $pathResolver
        );
    }

    public function testCacheRootPathDirCreationWithDoubledSlashes(): void
    {
        $pathResolver = new PathResolver(
            'aWebRootDir//subRootDir/',
            '/cachePrefix//subCacheDir'
        );
        $this->assertAttributeSame(
            'aWebRootDir/subRootDir',
            'webRoot',
            $pathResolver
        );
        $this->assertAttributeSame(
            'cachePrefix/subCacheDir',
            'cachePrefix',
            $pathResolver
        );
        $this->assertAttributeSame(
            'aWebRootDir/subRootDir/cachePrefix/subCacheDir',
            'cacheRoot',
            $pathResolver
        );
    }

    public function testGetCacheRoot(): void
    {
        $pathResolver = new PathResolver(
            'aWebRootDir',
            '/cachePrefix//subCacheDir'
        );
        $this->assertSame(
            'aWebRootDir/cachePrefix/subCacheDir',
            $pathResolver->getCacheRoot()
        );
    }

    public function testGetFileUrlWithSchemePath(): void
    {
        $path = 'https://path-to-no-where';
        $filter = 'aFilter';
        $cachePrefix = 'aCahcePrefix';

        $pathResolver = new PathResolver('aWebRootDir', $cachePrefix);
        $actualFileUrl = $pathResolver->getFileUrl($path, $filter);

        $this->assertSame(sprintf('%s/%s/https---path-to-no-where', $cachePrefix, $filter), $actualFileUrl);
    }

    public function testGetFileUrlPathTrim(): void
    {
        $path = '/path-to-no-where';
        $filter = 'aFilter';
        $cachePrefix = 'aCahcePrefix';

        $pathResolver = new PathResolver('aWebRootDir', $cachePrefix);
        $actualFileUrl = $pathResolver->getFileUrl($path, $filter);

        $this->assertSame(sprintf('%s/%s/path-to-no-where', $cachePrefix, $filter), $actualFileUrl);
    }

    public function testGetFilePathWithSchemePath(): void
    {
        $path = 'https://path-to-no-where';
        $filter = 'aFilter';
        $webRootDir = 'aWebRootDir';
        $cachePrefix = 'aCahcePrefix';

        $pathResolver = new PathResolver($webRootDir, $cachePrefix);
        $actualFileUrl = $pathResolver->getFilePath($path, $filter);

        $this->assertSame(
            sprintf('%s/%s/%s/https---path-to-no-where', $webRootDir, $cachePrefix, $filter),
            $actualFileUrl
        );
    }

    public function testGetFilePathWithPathTrim(): void
    {
        $path = '/path-to-no-where';
        $filter = 'aFilter';
        $webRootDir = 'aWebRootDir';
        $cachePrefix = 'aCahcePrefix';

        $pathResolver = new PathResolver($webRootDir, $cachePrefix);
        $actualFileUrl = $pathResolver->getFilePath($path, $filter);

        $this->assertSame(
            sprintf('%s/%s/%s/path-to-no-where', $webRootDir, $cachePrefix, $filter),
            $actualFileUrl
        );
    }
}
