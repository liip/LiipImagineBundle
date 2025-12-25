<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\DependencyInjection\Factory\Loader;

use Liip\ImagineBundle\DependencyInjection\Factory\Loader\AssetMapperLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\LoaderFactoryInterface;
use Liip\ImagineBundle\Tests\DependencyInjection\Factory\FactoryTestCase;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Factory\Loader\AssetMapperLoaderFactory<extended>
 */
class AssetMapperLoaderFactoryTest extends FactoryTestCase
{
    public function testImplementsLoaderFactoryInterface(): void
    {
        $this->assertInstanceOf(LoaderFactoryInterface::class, new AssetMapperLoaderFactory());
    }

    public function testReturnsExpectedName(): void
    {
        $this->assertSame('asset_mapper', (new AssetMapperLoaderFactory())->getName());
    }

    public function testCreateLoaderDefinition(): void
    {
        $container = new ContainerBuilder();

        $loader = new AssetMapperLoaderFactory();
        $loader->create($container, 'the_loader_name', [
        ]);

        $this->assertTrue($container->hasDefinition('liip_imagine.binary.loader.the_loader_name'));

        /** @var ChildDefinition $loaderDefinition */
        $loaderDefinition = $container->getDefinition('liip_imagine.binary.loader.the_loader_name');

        $this->assertInstanceOfChildDefinition($loaderDefinition);
        $this->assertSame('liip_imagine.binary.loader.prototype.filesystem', $loaderDefinition->getParent());
    }
}
