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
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class FileSystemLoaderFactory implements LoaderFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $loaderName, array $config)
    {
        $loaderDefinition = new DefinitionDecorator('liip_imagine.binary.loader.prototype.filesystem');
        $loaderDefinition->replaceArgument(2, $config['data_root']);
        $loaderDefinition->addTag('liip_imagine.binary.loader', array(
            'loader' => $loaderName,
        ));
        $loaderId = 'liip_imagine.binary.loader.'.$loaderName;

        $container->setDefinition($loaderId, $loaderDefinition);

        return $loaderId;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filesystem';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->arrayNode('data_root')
                    ->beforeNormalization()
                    ->ifString()
                        ->then(function ($value) { return array($value); })
                    ->end()
                    ->defaultValue(array('%kernel.root_dir%/../web'))
                    ->prototype('scalar')
                        ->cannotBeEmpty()
                    ->end()
                ->end()
            ->end();
    }
}
