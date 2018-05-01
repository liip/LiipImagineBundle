<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\Resolver\RelativeWebPathResolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Utility\Path\PathResolver;
use Liip\ImagineBundle\Utility\Path\PathResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @covers \Liip\ImagineBundle\Imagine\Cache\Resolver\RelativeWebPathResolver
 */
class RelativeWebPathResolverTest extends \PHPUnit\Framework\TestCase
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
    
    public function testImplementsResolverInterface()
    {
        $this->assertInstanceOf(ResolverInterface::class, $this->relativeWebPathResolver);
    }
    
    public function testResolve()
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
        
        $this->assertEquals(sprintf('/%s', $fileUrl), $actualFileUrl);
    }
    
    public function testOnSameConstructorArguments()
    {
        $this->assertAttributeEquals($this->filesystem,'filesystem', $this->relativeWebPathResolver);
        $this->assertAttributeEquals($this->pathResolverUtil,'pathResolver', $this->relativeWebPathResolver);
    }
    
    public function testFileIsStored()
    {
        $existingFile = $this->basePath.'/aCachePrefix/aFilter/existingPath';
        $filesystem = new Filesystem();
        $filesystem->mkdir(dirname($existingFile));
        $filesystem->touch($existingFile);
        
        $pathResolver = new PathResolver($this->basePath,'aCachePrefix');
        $resolver = new RelativeWebPathResolver(
            $this->filesystem,
            $pathResolver
        );
    
        $this->assertTrue($resolver->isStored('existingPath', 'aFilter'));
        $filesystem->remove($this->basePath);
    }
    
    public function testFileIsNotStored()
    {
        $existingFile = $this->basePath.'/aCachePrefix/aFilter/existingPath';
        $filesystem = new Filesystem();
        $filesystem->mkdir(dirname($existingFile));
        $filesystem->touch($existingFile);
        
        $pathResolver = new PathResolver($this->basePath,'aCachePrefix');
        $resolver = new RelativeWebPathResolver(
            $this->filesystem,
            $pathResolver
        );
        
        $this->assertFalse($resolver->isStored('notExisting file', 'aFilter'));
        $filesystem->remove($this->basePath);
    }
    
    public function testStore()
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
    
    public function testRemoveWithEmptyInputArrays()
    {
        $this->filesystem
            ->expects($this->exactly(0))
            ->method('remove');
        
        $this->relativeWebPathResolver->remove(array(), array());
    }
    
    public function testRemoveWithEmptyPathsArrayAndSingleFilter()
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
        
        $this->relativeWebPathResolver->remove(array(), array($filter));
    }
    
    public function testRemoveWithEmptyPathsArrayAndMultipleFilters()
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
        
        $this->relativeWebPathResolver->remove(array(), array($filterOne, $filterTwo));
    }
    
    public function testRemoveWithMultiplePathaAndFilters()
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
            ->method("getFilePath")
            ->willReturnMap(
                [
                    [$pathOne, $filterOne, sprintf("%s/%s/%s", $cacheRoot, $filterOne, $pathOne)],
                    [$pathOne, $filterTwo, sprintf("%s/%s/%s", $cacheRoot, $filterTwo, $pathOne)],
                    [$pathTwo, $filterOne, sprintf("%s/%s/%s", $cacheRoot, $filterOne, $pathTwo)],
                    [$pathTwo, $filterTwo, sprintf("%s/%s/%s", $cacheRoot, $filterTwo, $pathTwo)],
                ]
            );
            
        $this->filesystem
            ->expects($this->at(0))
            ->method('remove')
            ->with(sprintf("%s/%s/%s", $cacheRoot, $filterOne, $pathOne));
        $this->filesystem
            ->expects($this->at(1))
            ->method('remove')
            ->with(sprintf("%s/%s/%s", $cacheRoot, $filterTwo, $pathOne));
        $this->filesystem
            ->expects($this->at(2))
            ->method('remove')
            ->with(sprintf("%s/%s/%s", $cacheRoot, $filterOne, $pathTwo));
        $this->filesystem
            ->expects($this->at(3))
            ->method('remove')
            ->with(sprintf("%s/%s/%s", $cacheRoot, $filterTwo, $pathTwo));
        

        $this->relativeWebPathResolver->remove(
            array($pathOne, $pathTwo),
            array($filterOne, $filterTwo)
        );
    }
}