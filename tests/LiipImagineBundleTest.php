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

use Liip\ImagineBundle\DependencyInjection\Compiler\AssetsVersionCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\DriverCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\FiltersCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\LoadersCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\MetadataReaderCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\PostProcessorsCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\ResolversCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\ChainLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\FileSystemLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\FlysystemLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\LoaderFactoryInterface;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\StreamLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\AwsS3ResolverFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\FlysystemResolverFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\ResolverFactoryInterface;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\WebPathResolverFactory;
use Liip\ImagineBundle\DependencyInjection\LiipImagineExtension;
use Liip\ImagineBundle\LiipImagineBundle;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @covers \Liip\ImagineBundle\LiipImagineBundle
 */
class LiipImagineBundleTest extends AbstractTest
{
    public function testSubClassOfBundle(): void
    {
        $this->assertInstanceOf(Bundle::class, new LiipImagineBundle());
    }

    public function testAddPasses(): void
    {
        $passes = [];
        $containerMock = $this->createMock(ContainerBuilder::class);
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->willReturn($this->createLiipImagineExtensionMock());
        $containerMock
            ->method('addCompilerPass')
            ->with($this->callback(static function (CompilerPassInterface $pass) use (&$passes): bool {
                $passes[] = $pass::class;

                return true;
            }));

        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);

        $this->assertCount(7, $passes);

        sort($passes);

        $this->assertSame([
            AssetsVersionCompilerPass::class,
            DriverCompilerPass::class,
            FiltersCompilerPass::class,
            LoadersCompilerPass::class,
            MetadataReaderCompilerPass::class,
            PostProcessorsCompilerPass::class,
            ResolversCompilerPass::class,
        ], $passes);
    }

    public function testAddResolvers(): void
    {
        $resolvers = [];
        $extensionMock = $this->createLiipImagineExtensionMock();
        $extensionMock
            ->method('addResolverFactory')
            ->with($this->callback(static function (ResolverFactoryInterface $resolver) use (&$resolvers): bool {
                $resolvers[] = $resolver::class;

                return true;
            }));

        $containerMock = $this->createMock(ContainerBuilder::class);
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->willReturn($extensionMock);

        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);

        $this->assertSame([
            WebPathResolverFactory::class,
            AwsS3ResolverFactory::class,
            FlysystemResolverFactory::class,
        ], $resolvers);
    }

    public function testAddLoaders(): void
    {
        $loaders = [];
        $extensionMock = $this->createLiipImagineExtensionMock();
        $extensionMock
            ->method('addLoaderFactory')
            ->with($this->callback(static function (LoaderFactoryInterface $loader) use (&$loaders): bool {
                $loaders[] = $loader::class;

                return true;
            }));
        $containerMock = $this->createMock(ContainerBuilder::class);
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->willReturn($extensionMock);
        $bundle = new LiipImagineBundle();
        $bundle->build($containerMock);

        $this->assertSame([
            StreamLoaderFactory::class,
            FileSystemLoaderFactory::class,
            FlysystemLoaderFactory::class,
            ChainLoaderFactory::class,
        ], $loaders);
    }

    /**
     * @return MockObject&LiipImagineExtension
     */
    protected function createLiipImagineExtensionMock()
    {
        $builder = $this->getMockBuilder(LiipImagineExtension::class);
        $builder->setMethods([
            'getNamespace',
            'addResolverFactory',
            'addLoaderFactory',
        ]);

        $builder->disableOriginalConstructor();

        return $builder->getMock();
    }
}
