<?php

namespace Liip\ImagineBundle\DependencyInjection;

use Liip\ImagineBundle\DependencyInjection\Factory\Loader\LoaderFactoryInterface;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\ResolverFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @var ResolverFactoryInterface[]
     */
    protected $resolversFactories;

    /**
     * @var LoaderFactoryInterface[]
     */
    protected $loadersFactories;

    /**
     * @param ResolverFactoryInterface[] $resolversFactories
     * @param LoaderFactoryInterface[] $loadersFactories
     */
    public function __construct(array $resolversFactories, array $loadersFactories)
    {
        $this->resolversFactories = $resolversFactories;
        $this->loadersFactories = $loadersFactories;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('liip_imagine', 'array');

        $resolversPrototypeNode = $rootNode
            ->children()
                ->arrayNode('resolvers')
                ->beforeNormalization()
                    ->ifTrue(function ($v) { return !is_array($v) || (is_array($v) && !array_key_exists('default', $v)); })
                    ->then(function ($v) {
                        if (false == is_array($v)) {
                            $v = array();
                        }

                        $v['default'] = array('web_path' => null);

                        return $v;
                    })
                ->end()
                ->useAttributeAsKey('name')
                ->prototype('array')
        ;
        $this->addResolversSections($resolversPrototypeNode);

        $loadersPrototypeNode = $rootNode
            ->children()
                ->arrayNode('loaders')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
        ;
        $this->addLoadersSections($loadersPrototypeNode);

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
                ->scalarNode('data_root')->defaultValue('%liip_imagine.web_root%')->end()
                ->scalarNode('cache')->defaultValue('default')->end()
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
                            ->scalarNode('cache')->defaultNull()->end()
                            ->scalarNode('data_loader')->defaultNull()->end()
                            ->scalarNode('controller_action')->defaultNull()->end()
                            ->arrayNode('route')
                                ->defaultValue(array())
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->useAttributeAsKey('name')
                                    ->prototype('variable')->end()
                                ->end()
                            ->end()
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

    /**
     * @param ArrayNodeDefinition $resolversPrototypeNode
     */
    protected function addResolversSections(ArrayNodeDefinition $resolversPrototypeNode)
    {
        foreach ($this->resolversFactories as $factory) {
            $factory->addConfiguration(
                $resolversPrototypeNode->children()->arrayNode($factory->getName())
            );
        }
    }

    /**
     * @param ArrayNodeDefinition $resolversPrototypeNode
     */
    protected function addLoadersSections(ArrayNodeDefinition $resolversPrototypeNode)
    {
        foreach ($this->loadersFactories as $factory) {
            $factory->addConfiguration(
                $resolversPrototypeNode->children()->arrayNode($factory->getName())
            );
        }
    }
}
