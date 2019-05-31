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

use Liip\ImagineBundle\DependencyInjection\Compiler\FiltersCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\LoadersCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\PostProcessorsCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\ResolversCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\ChainLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\FileSystemLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\FlysystemLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\StreamLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\AwsS3ResolverFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\FlysystemResolverFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\WebPathResolverFactory;
use Liip\ImagineBundle\DependencyInjection\LiipImagineExtension;
use Liip\ImagineBundle\LiipImagineBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @covers \Liip\ImagineBundle\LiipImagineBundle
 */
class LiipImagineBundleTest extends AbstractTest
{
    public function testSubClassOfBundle()
    {
        $this->assertInstanceOf(Bundle::class, new LiipImagineBundle());
    }

    public function testAddLoadersCompilerPassOnBuild()
    {
        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->willReturn($this->createLiipImagineExtensionMock());
        $containerMock
            ->expects($this->at(1))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(LoadersCompilerPass::class));

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
            ->willReturn($this->createLiipImagineExtensionMock());
        $containerMock
            ->expects($this->at(2))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(FiltersCompilerPass::class));

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
            ->willReturn($this->createLiipImagineExtensionMock());
        $containerMock
            ->expects($this->at(3))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(PostProcessorsCompilerPass::class));

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
            ->willReturn($this->createLiipImagineExtensionMock());
        $containerMock
            ->expects($this->at(4))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(ResolversCompilerPass::class));

        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);
    }

    public function testAddWebPathResolverFactoryOnBuild()
    {
        $extensionMock = $this->createLiipImagineExtensionMock();
        $extensionMock
            ->expects($this->at(0))
            ->method('addResolverFactory')
            ->with($this->isInstanceOf(WebPathResolverFactory::class));

        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->willReturn($extensionMock);

        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);
    }

    public function testAddAwsS3ResolverFactoryOnBuild()
    {
        $extensionMock = $this->createLiipImagineExtensionMock();
        $extensionMock
            ->expects($this->at(1))
            ->method('addResolverFactory')
            ->with($this->isInstanceOf(AwsS3ResolverFactory::class));

        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->willReturn($extensionMock);

        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);
    }

    public function testAddFlysystemResolverFactoryOnBuild()
    {
        $extensionMock = $this->createLiipImagineExtensionMock();
        $extensionMock
            ->expects($this->at(2))
            ->method('addResolverFactory')
            ->with($this->isInstanceOf(FlysystemResolverFactory::class));

        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->willReturn($extensionMock);

        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);
    }

    public function testAddChainLoaderFactoryOnBuild()
    {
        $extensionMock = $this->createLiipImagineExtensionMock();
        $extensionMock
            ->expects($this->at(6))
            ->method('addLoaderFactory')
            ->with($this->isInstanceOf(ChainLoaderFactory::class));
        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->willReturn($extensionMock);
        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);
    }

    public function testAddStreamLoaderFactoryOnBuild()
    {
        $extensionMock = $this->createLiipImagineExtensionMock();
        $extensionMock
            ->expects($this->at(3))
            ->method('addLoaderFactory')
            ->with($this->isInstanceOf(StreamLoaderFactory::class));

        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->willReturn($extensionMock);

        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);
    }

    public function testAddFilesystemLoaderFactoryOnBuild()
    {
        $extensionMock = $this->createLiipImagineExtensionMock();
        $extensionMock
            ->expects($this->at(4))
            ->method('addLoaderFactory')
            ->with($this->isInstanceOf(FileSystemLoaderFactory::class));

        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->willReturn($extensionMock);

        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);
    }

    public function testAddFlysystemLoaderFactoryOnBuild()
    {
        $extensionMock = $this->createLiipImagineExtensionMock();
        $extensionMock
            ->expects($this->at(5))
            ->method('addLoaderFactory')
            ->with($this->isInstanceOf(FlysystemLoaderFactory::class));

        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->willReturn($extensionMock);

        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ContainerBuilder
     */
    protected function createContainerBuilderMock()
    {
        return $this->createObjectMock(ContainerBuilder::class, [], false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LiipImagineExtension
     */
    protected function createLiipImagineExtensionMock()
    {
        return $this->createObjectMock(LiipImagineExtension::class, [
            'getNamespace',
            'addResolverFactory',
            'addLoaderFactory',
        ], false);
    }
}
