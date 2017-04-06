<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests;

use Liip\ImagineBundle\DependencyInjection\LiipImagineExtension;
use Liip\ImagineBundle\LiipImagineBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Liip\ImagineBundle\LiipImagineBundle
 */
class LiipImagineBundleTest extends AbstractTest
{
    public function testSubClassOfBundle()
    {
        $this->assertInstanceOf('Symfony\Component\HttpKernel\Bundle\Bundle', new LiipImagineBundle());
    }

    public function testLocatorsCompilerPassOnBuild()
    {
        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->will($this->returnValue($this->createLiipImagineExtensionMock()));
        $containerMock
            ->expects($this->at(0))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('Liip\ImagineBundle\DependencyInjection\Compiler\LocatorsCompilerPass'));

        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);
    }

    public function testAddLoadersCompilerPassOnBuild()
    {
        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->will($this->returnValue($this->createLiipImagineExtensionMock()));
        $containerMock
            ->expects($this->at(1))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('Liip\ImagineBundle\DependencyInjection\Compiler\LoadersCompilerPass'));

        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);
    }

    public function testAddFiltersCompilerPassOnBuild()
    {
        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->will($this->returnValue($this->createLiipImagineExtensionMock()));
        $containerMock
            ->expects($this->at(2))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('Liip\ImagineBundle\DependencyInjection\Compiler\FiltersCompilerPass'));

        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);
    }

    public function testAddPostProcessorsCompilerPassOnBuild()
    {
        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->will($this->returnValue($this->createLiipImagineExtensionMock()));
        $containerMock
            ->expects($this->at(3))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('Liip\ImagineBundle\DependencyInjection\Compiler\PostProcessorsCompilerPass'));

        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);
    }

    public function testAddResolversCompilerPassOnBuild()
    {
        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->will($this->returnValue($this->createLiipImagineExtensionMock()));
        $containerMock
            ->expects($this->at(4))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('Liip\ImagineBundle\DependencyInjection\Compiler\ResolversCompilerPass'));

        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);
    }

    public function testAddWebPathResolverFactoryOnBuild()
    {
        $extensionMock = $this->createLiipImagineExtensionMock();
        $extensionMock
            ->expects($this->at(0))
            ->method('addResolverFactory')
            ->with($this->isInstanceOf('Liip\ImagineBundle\DependencyInjection\Factory\Resolver\WebPathResolverFactory'));

        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->will($this->returnValue($extensionMock));

        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);
    }

    public function testAddAwsS3ResolverFactoryOnBuild()
    {
        $extensionMock = $this->createLiipImagineExtensionMock();
        $extensionMock
            ->expects($this->at(1))
            ->method('addResolverFactory')
            ->with($this->isInstanceOf('Liip\ImagineBundle\DependencyInjection\Factory\Resolver\AwsS3ResolverFactory'));

        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->will($this->returnValue($extensionMock));

        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);
    }

    public function testAddFlysystemResolverFactoryOnBuild()
    {
        $extensionMock = $this->createLiipImagineExtensionMock();
        $extensionMock
            ->expects($this->at(2))
            ->method('addResolverFactory')
            ->with($this->isInstanceOf('Liip\ImagineBundle\DependencyInjection\Factory\Resolver\FlysystemResolverFactory'));

        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->will($this->returnValue($extensionMock));

        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);
    }

    public function testAddStreamLoaderFactoryOnBuild()
    {
        $extensionMock = $this->createLiipImagineExtensionMock();
        $extensionMock
            ->expects($this->at(3))
            ->method('addLoaderFactory')
            ->with($this->isInstanceOf('Liip\ImagineBundle\DependencyInjection\Factory\Loader\StreamLoaderFactory'));

        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->will($this->returnValue($extensionMock));

        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);
    }

    public function testAddFilesystemLoaderFactoryOnBuild()
    {
        $extensionMock = $this->createLiipImagineExtensionMock();
        $extensionMock
            ->expects($this->at(4))
            ->method('addLoaderFactory')
            ->with($this->isInstanceOf('Liip\ImagineBundle\DependencyInjection\Factory\Loader\FilesystemLoaderFactory'));

        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->will($this->returnValue($extensionMock));

        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);
    }

    public function testAddFlysystemLoaderFactoryOnBuild()
    {
        $extensionMock = $this->createLiipImagineExtensionMock();
        $extensionMock
            ->expects($this->at(5))
            ->method('addLoaderFactory')
            ->with($this->isInstanceOf('Liip\ImagineBundle\DependencyInjection\Factory\Loader\FlysystemLoaderFactory'));

        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->will($this->returnValue($extensionMock));

        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ContainerBuilder
     */
    protected function createContainerBuilderMock()
    {
        return $this->createObjectMock('Symfony\Component\DependencyInjection\ContainerBuilder', array(), false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LiipImagineExtension
     */
    protected function createLiipImagineExtensionMock()
    {
        return $this->createObjectMock('Liip\ImagineBundle\DependencyInjection\LiipImagineExtension', array(
            'getNamespace',
            'addResolverFactory',
            'addLoaderFactory',
        ), false);
    }
}
