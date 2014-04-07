<?php
namespace Liip\ImagineBundle\Tests;

use Liip\ImagineBundle\DependencyInjection\LiipImagineExtension;
use Liip\ImagineBundle\LiipImagineBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LiipImagineBundleTest extends \Phpunit_Framework_TestCase
{
    public function testSubClassOfBundle()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\LiipImagineBundle');

        $this->assertTrue($rc->isSubclassOf('Symfony\Component\HttpKernel\Bundle\Bundle'));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new LiipImagineBundle;
    }

    public function testAddLoadersCompilerPassOnBuild()
    {
        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->will($this->returnValue($this->createExtensionMock()))
        ;
        $containerMock
            ->expects($this->at(0))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('Liip\ImagineBundle\DependencyInjection\Compiler\LoadersCompilerPass'))
        ;

        $bundle = new LiipImagineBundle;

        $bundle->build($containerMock);
    }

    public function testAddFiltersCompilerPassOnBuild()
    {
        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->will($this->returnValue($this->createExtensionMock()))
        ;
        $containerMock
            ->expects($this->at(1))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('Liip\ImagineBundle\DependencyInjection\Compiler\FiltersCompilerPass'))
        ;

        $bundle = new LiipImagineBundle;

        $bundle->build($containerMock);
    }

    public function testAddResolversCompilerPassOnBuild()
    {
        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->will($this->returnValue($this->createExtensionMock()))
        ;
        $containerMock
            ->expects($this->at(2))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('Liip\ImagineBundle\DependencyInjection\Compiler\ResolversCompilerPass'))
        ;

        $bundle = new LiipImagineBundle;

        $bundle->build($containerMock);
    }

    public function testAddWebPathResolverFactoryOnBuild()
    {
        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->at(0))
            ->method('addResolverFactory')
            ->with($this->isInstanceOf('Liip\ImagineBundle\DependencyInjection\Factory\Resolver\WebPathResolverFactory'))
        ;

        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->will($this->returnValue($extensionMock))
        ;

        $bundle = new LiipImagineBundle;

        $bundle->build($containerMock);
    }

    public function testAddAwsS3ResolverFactoryOnBuild()
    {
        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->at(1))
            ->method('addResolverFactory')
            ->with($this->isInstanceOf('Liip\ImagineBundle\DependencyInjection\Factory\Resolver\AwsS3ResolverFactory'))
        ;

        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->will($this->returnValue($extensionMock))
        ;

        $bundle = new LiipImagineBundle;

        $bundle->build($containerMock);
    }

    public function testAddStreamLoaderFactoryOnBuild()
    {
        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->at(2))
            ->method('addLoaderFactory')
            ->with($this->isInstanceOf('Liip\ImagineBundle\DependencyInjection\Factory\Loader\StreamLoaderFactory'))
        ;

        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->will($this->returnValue($extensionMock))
        ;

        $bundle = new LiipImagineBundle;

        $bundle->build($containerMock);
    }

    public function testAddFilesystemLoaderFactoryOnBuild()
    {
        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->at(3))
            ->method('addLoaderFactory')
            ->with($this->isInstanceOf('Liip\ImagineBundle\DependencyInjection\Factory\Loader\FilesystemLoaderFactory'))
        ;

        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->will($this->returnValue($extensionMock))
        ;

        $bundle = new LiipImagineBundle;

        $bundle->build($containerMock);
    }

    protected function createContainerBuilderMock()
    {
        return $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder', array(), array(), '', false);
    }

    protected function createExtensionMock()
    {
        $methods = array(
            'getNamespace', 'addResolverFactory', 'addLoaderFactory'
        );

        return $this->getMock('Liip\ImagineBundle\DependencyInjection\LiipImagineExtension', $methods, array(), '', false);
    }

} 