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

use League\Flysystem\FilesystemOperator;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FlysystemLoaderFactory extends AbstractLoaderFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $loaderName, array $config)
    {
        $definition = $this->getChildLoaderDefinition($this->getChildLoaderName());

        if ($container->hasDefinition('liip_imagine.mime_types')) {
            $mimeTypes = $container->getDefinition('liip_imagine.mime_types');
            $definition->replaceArgument(0, $mimeTypes);
        }

        $definition->replaceArgument(1, new Reference($config['filesystem_service']));

        return $this->setTaggedLoaderDefinition($loaderName, $definition, $container);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'flysystem';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('filesystem_service')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            ->end();
    }

    /**
     * @return string|null
     */
    private function getChildLoaderName()
    {
        if (interface_exists(FilesystemOperator::class)) {
            return 'flysystem2';
        }

        return null;
    }
}
