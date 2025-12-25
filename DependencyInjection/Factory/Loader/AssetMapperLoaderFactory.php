<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\DependencyInjection\Factory\Loader;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AssetMapperLoaderFactory extends AbstractLoaderFactory
{
    public function create(ContainerBuilder $container, $loaderName, array $config)
    {
        $locatorDefinition = new ChildDefinition('liip_imagine.binary.locator.asset_mapper');
        $locatorDefinition->replaceArgument(0, new Reference('asset_mapper'));

        $definition = $this->getChildLoaderDefinition('filesystem');

        if ($container->hasDefinition('liip_imagine.mime_types')) {
            $mimeTypes = $container->getDefinition('liip_imagine.mime_types');
            $definition->replaceArgument(0, $mimeTypes);
            $definition->replaceArgument(1, $mimeTypes);
        }
        $definition->replaceArgument(2, $locatorDefinition);

        return $this->setTaggedLoaderDefinition($loaderName, $definition, $container);
    }

    public function getName()
    {
        return 'asset_mapper';
    }

    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
            ->end();
    }
}
