<?php

namespace Liip\ImagineBundle\Tests\Utility\Path;

use Liip\ImagineBundle\Utility\Path\PathResolver;
use Liip\ImagineBundle\Utility\Path\PathResolverInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Utility\Path\PathResolver
 */
class PathResolverTest extends TestCase
{
    public function testForInterfaceImplementation()
    {
        $this->assertTrue(is_a(PathResolver::class, PathResolverInterface::class, true));
    }
    
    public function testPropertiesForSameConstructorArguments()
    {
        $webRootDir = 'aWebRootDir';
        $cachePrefix = 'aCachePrefix';
        $pathResolver = new PathResolver($webRootDir, $cachePrefix);
        $this->assertAttributeEquals($webRootDir, 'webRoot', $pathResolver);
        $this->assertAttributeEquals($cachePrefix, 'cachePrefix', $pathResolver);
    }
    
    public function testWebRootPathNormalizer()
    {
        $pathResolver = new PathResolver('aWebRootDir/');
        $this->assertAttributeEquals('aWebRootDir', 'webRoot', $pathResolver);
    }
    
    public function testCachePrefixNormalizer()
    {
        $pathResolver = new PathResolver('aWebRootDir', '/cachePrefix');
        $this->assertAttributeEquals('cachePrefix', 'cachePrefix', $pathResolver);
    }
    
    public function testForDoubleSlashReplacing()
    {
        $pathResolver = new PathResolver(
            'aWebRootDir//subRootDir',
            'cachePrefix//subCacheDir'
        );
        $this->assertAttributeEquals(
            'aWebRootDir/subRootDir',
            'webRoot',
            $pathResolver
        );
        $this->assertAttributeEquals(
            'cachePrefix/subCacheDir',
            'cachePrefix',
            $pathResolver
        );
    }
    
    public function testPathsNormalizerWithSubfolders()
    {
        $pathResolver = new PathResolver(
            'aWebRootDir/subRootDir',
            'cachePrefix/subCacheDir/anotherSubCacheDir'
        );
        $this->assertAttributeEquals(
            'aWebRootDir/subRootDir',
            'webRoot',
            $pathResolver
        );
        $this->assertAttributeEquals(
            'cachePrefix/subCacheDir/anotherSubCacheDir',
            'cachePrefix',
            $pathResolver
        );
    }
    
    public function testCacheRootPathDirCreationWithoutInvalidSlashes()
    {
        $pathResolver = new PathResolver(
            'aWebRootDir/subRootDir',
            'cachePrefix/subCacheDir'
        );
        $this->assertAttributeEquals(
            'aWebRootDir/subRootDir',
            'webRoot',
            $pathResolver
        );
        $this->assertAttributeEquals(
            'cachePrefix/subCacheDir',
            'cachePrefix',
            $pathResolver
        );
        $this->assertAttributeEquals(
            'aWebRootDir/subRootDir/cachePrefix/subCacheDir',
            'cacheRoot',
            $pathResolver
        );
    }
    
    public function testCacheRootPathDirCreationWithInvalidSlashes()
    {
        $pathResolver = new PathResolver(
            'aWebRootDir/subRootDir/',
            '/cachePrefix/subCacheDir'
        );
        $this->assertAttributeEquals(
            'aWebRootDir/subRootDir',
            'webRoot',
            $pathResolver
        );
        $this->assertAttributeEquals(
            'cachePrefix/subCacheDir',
            'cachePrefix',
            $pathResolver
        );
        $this->assertAttributeEquals(
            'aWebRootDir/subRootDir/cachePrefix/subCacheDir',
            'cacheRoot',
            $pathResolver
        );
    }
    
    public function testCacheRootPathDirCreationWithDoubledSlashes()
    {
        $pathResolver = new PathResolver(
            'aWebRootDir//subRootDir/',
            '/cachePrefix//subCacheDir'
        );
        $this->assertAttributeEquals(
            'aWebRootDir/subRootDir',
            'webRoot',
            $pathResolver
        );
        $this->assertAttributeEquals(
            'cachePrefix/subCacheDir',
            'cachePrefix',
            $pathResolver
        );
        $this->assertAttributeEquals(
            'aWebRootDir/subRootDir/cachePrefix/subCacheDir',
            'cacheRoot',
            $pathResolver
        );
    }
    
    public function testGetCacheRoot()
    {
        $pathResolver = new PathResolver(
            'aWebRootDir',
            '/cachePrefix//subCacheDir'
        );
        $this->assertEquals(
            'aWebRootDir/cachePrefix/subCacheDir',
            $pathResolver->getCacheRoot()
        );
    }
    
    public function testGetFileUrlWithSchemePath()
    {
        $path = 'https://path-to-no-where';
        $filter = 'aFilter';
        $cachePrefix = 'aCahcePrefix';
        
        $pathResolver = new PathResolver('aWebRootDir', $cachePrefix);
        $actualFileUrl = $pathResolver->getFileUrl($path, $filter);
        
        $this->assertEquals(sprintf('%s/%s/https---path-to-no-where', $cachePrefix, $filter), $actualFileUrl);
    }
    
    public function testGetFileUrlPathTrim()
    {
        $path = '/path-to-no-where';
        $filter = 'aFilter';
        $cachePrefix = 'aCahcePrefix';
        
        $pathResolver = new PathResolver('aWebRootDir', $cachePrefix);
        $actualFileUrl = $pathResolver->getFileUrl($path, $filter);
        
        $this->assertEquals(sprintf('%s/%s/path-to-no-where', $cachePrefix, $filter), $actualFileUrl);
    }
    
    public function testGetFilePathWithSchemePath()
    {
        $path = 'https://path-to-no-where';
        $filter = 'aFilter';
        $webRootDir = 'aWebRootDir';
        $cachePrefix = 'aCahcePrefix';
        
        $pathResolver = new PathResolver($webRootDir, $cachePrefix);
        $actualFileUrl = $pathResolver->getFilePath($path, $filter);
        
        $this->assertEquals(
            sprintf('%s/%s/%s/https---path-to-no-where', $webRootDir, $cachePrefix, $filter),
            $actualFileUrl
        );
    }
    
    public function testGetFilePathWithPathTrim()
    {
        $path = '/path-to-no-where';
        $filter = 'aFilter';
        $webRootDir = 'aWebRootDir';
        $cachePrefix = 'aCahcePrefix';
        
        $pathResolver = new PathResolver($webRootDir, $cachePrefix);
        $actualFileUrl = $pathResolver->getFilePath($path, $filter);
        
        $this->assertEquals(
            sprintf('%s/%s/%s/path-to-no-where', $webRootDir, $cachePrefix, $filter),
            $actualFileUrl
        );
    }
}
