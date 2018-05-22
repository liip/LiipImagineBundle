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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ChainLoaderFactory extends AbstractLoaderFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $loaderName, array $config): string
    {
        $definition = $this->getChildLoaderDefinition();
        $definition->replaceArgument(0, $this->createLoaderReferences($config['loaders']));

        return $this->setTaggedLoaderDefinition($loaderName, $definition, $container);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'chain';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
                ->arrayNode('loaders')
                    ->isRequired()
                    ->prototype('scalar')
                        ->cannotBeEmpty()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param string[] $loaders
     *
     * @return string[]
     */
    private function createLoaderReferences(array $loaders): array
    {
        return array_combine($loaders, array_map(function ($name) {
            return new Reference(sprintf('liip_imagine.binary.loader.%s', $name));
        }, $loaders));
    }
}
