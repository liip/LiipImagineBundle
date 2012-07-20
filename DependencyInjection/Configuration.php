<?php

namespace Liip\ImagineBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder,
    Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('liip_imagine', 'array');

        $rootNode
            ->fixXmlConfig('format', 'formats')
            ->fixXmlConfig('filter_set', 'filter_sets')
            ->children()
                ->scalarNode('driver')->defaultValue('gd')
                    ->validate()
                        ->ifTrue(function($v) { return !in_array($v, array('gd', 'imagick', 'gmagick')); })
                        ->thenInvalid('Invalid imagine driver specified: %s')
                    ->end()
                ->end()
                ->scalarNode('web_root')->defaultValue('%kernel.root_dir%/../web')->end()
                ->scalarNode('data_root')->defaultValue('%liip_imagine.web_root%')->end()
                ->scalarNode('cache_prefix')->defaultValue('/media/cache')->end()
                ->scalarNode('cache')->defaultValue('web_path')->end()
                ->scalarNode('cache_base_path')->defaultValue('')->end()
                ->scalarNode('data_loader')->defaultValue('filesystem')->end()
                ->scalarNode('controller_action')->defaultValue('liip_imagine.controller:filterAction')->end()
                ->arrayNode('formats')
                    ->defaultValue(array())
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('filter_sets')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->fixXmlConfig('filter', 'filters')
                        ->children()
                            ->scalarNode('path')->end()
                            ->scalarNode('quality')->defaultValue(100)->end()
                            ->scalarNode('format')->defaultNull()->end()
                            ->scalarNode('cache')->defaultNull()->end()
                            ->scalarNode('data_loader')->defaultNull()->end()
                            ->scalarNode('controller_action')->defaultNull()->end()
                            ->arrayNode('filters')
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->useAttributeAsKey('name')
                                    ->prototype('variable')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
