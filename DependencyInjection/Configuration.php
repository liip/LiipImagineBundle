<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\DependencyInjection;

use Liip\ImagineBundle\Config\Controller\ControllerConfig;
use Liip\ImagineBundle\Controller\ImagineController;
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
     * @param LoaderFactoryInterface[]   $loadersFactories
     */
    public function __construct(array $resolversFactories, array $loadersFactories)
    {
        $this->resolversFactories = $resolversFactories;
        $this->loadersFactories = $loadersFactories;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('liip_imagine');
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('liip_imagine');

        $resolversPrototypeNode = $rootNode
            ->children()
                ->arrayNode('resolvers')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->performNoDeepMerging();
        $this->addResolversSections($resolversPrototypeNode);

        $loadersPrototypeNode = $rootNode
            ->children()
                ->arrayNode('loaders')
                    ->useAttributeAsKey('name')
                    ->prototype('array');
        $this->addLoadersSections($loadersPrototypeNode);

        $rootNode
            ->beforeNormalization()
                ->ifTrue(function ($v) {
                    return
                        empty($v['loaders']) ||
                        empty($v['loaders']['default']) ||
                        empty($v['resolvers']) ||
                        empty($v['resolvers']['default']);
                })
                ->then(function ($v) {
                    if (empty($v['loaders'])) {
                        $v['loaders'] = [];
                    }

                    if (false === \is_array($v['loaders'])) {
                        throw new \LogicException('Loaders has to be array');
                    }

                    if (false === \array_key_exists('default', $v['loaders'])) {
                        $v['loaders']['default'] = ['filesystem' => null];
                    }

                    if (empty($v['resolvers'])) {
                        $v['resolvers'] = [];
                    }

                    if (false === \is_array($v['resolvers'])) {
                        throw new \LogicException('Resolvers has to be array');
                    }

                    if (false === \array_key_exists('default', $v['resolvers'])) {
                        $v['resolvers']['default'] = ['web_path' => null];
                    }

                    return $v;
                })
            ->end();

        $rootNode
            ->fixXmlConfig('filter_set', 'filter_sets')
            ->children()
                ->scalarNode('driver')->defaultValue('gd')
                    ->validate()
                        ->ifTrue(function ($v) {
                            return !\in_array($v, ['gd', 'imagick', 'gmagick'], true);
                        })
                        ->thenInvalid('Invalid imagine driver specified: %s')
                    ->end()
                ->end()
                ->scalarNode('cache')->defaultValue('default')->end()
                ->scalarNode('cache_base_path')->defaultValue('')->end()
                ->scalarNode('data_loader')->defaultValue('default')->end()
                ->scalarNode('default_image')->defaultNull()->end()
                ->arrayNode('default_filter_set_settings')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('quality')->defaultValue(100)->end()
                        ->scalarNode('jpeg_quality')->defaultNull()->end()
                        ->scalarNode('png_compression_level')->defaultNull()->end()
                        ->scalarNode('png_compression_filter')->defaultNull()->end()
                        ->scalarNode('format')->defaultNull()->end()
                        ->booleanNode('animated')->defaultFalse()->end()
                        ->scalarNode('cache')->defaultNull()->end()
                        ->scalarNode('data_loader')->defaultNull()->end()
                        ->scalarNode('default_image')->defaultNull()->end()
                        ->arrayNode('filters')
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->useAttributeAsKey('name')
                                ->prototype('variable')->end()
                            ->end()
                        ->end()
                        ->arrayNode('post_processors')
                            ->defaultValue([])
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->useAttributeAsKey('name')
                                ->prototype('variable')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('controller')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('filter_action')->defaultValue(sprintf('%s::filterAction', ImagineController::class))->end()
                        ->scalarNode('filter_runtime_action')->defaultValue(sprintf('%s::filterRuntimeAction', ImagineController::class))->end()
                        ->integerNode('redirect_response_code')->defaultValue(301)
                            ->validate()
                                ->ifTrue(function ($redirectResponseCode) {
                                    return !\in_array($redirectResponseCode, ControllerConfig::REDIRECT_RESPONSE_CODES, true);
                                })
                                ->thenInvalid('Invalid redirect response code "%s" (must be 201, 301, 302, 303, 307, or 308).')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('filter_sets')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->fixXmlConfig('filter', 'filters')
                        ->children()
                            ->scalarNode('quality')->end()
                            ->scalarNode('jpeg_quality')->end()
                            ->scalarNode('png_compression_level')->end()
                            ->scalarNode('png_compression_filter')->end()
                            ->scalarNode('format')->end()
                            ->booleanNode('animated')->end()
                            ->scalarNode('cache')->end()
                            ->scalarNode('data_loader')->end()
                            ->scalarNode('default_image')->end()
                            ->arrayNode('filters')
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->useAttributeAsKey('name')
                                    ->prototype('variable')->end()
                                ->end()
                            ->end()
                            ->arrayNode('post_processors')
                                ->defaultValue([])
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->useAttributeAsKey('name')
                                    ->prototype('variable')->end()
                                ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->booleanNode('enqueue')
                ->defaultFalse()
                ->info('Enables integration with enqueue if set true. Allows resolve image caches in background by sending messages to MQ.')
            ->end()
            ->booleanNode('templating')
                ->defaultTrue()
                ->info('Enables integration with symfony/templating component')
                ->validate()
                    ->ifTrue()
                    ->then(function ($v) {
                        @trigger_error('Symfony templating integration has been deprecated since LiipImagineBundle 2.2 and will be removed in 3.0. Use Twig and use "false" as "liip_imagine.templating" value instead.', E_USER_DEPRECATED);

                        return $v;
                    })
                ->end()
            ->end()
        ->end();

        $rootNode
            ->children()
                ->arrayNode('webp')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('generate')->defaultFalse()->end()
                        ->integerNode('quality')->defaultValue(100)->end()
                        ->scalarNode('cache')->defaultNull()->end()
                        ->scalarNode('data_loader')->defaultNull()->end()
                        ->arrayNode('post_processors')
                            ->defaultValue([])
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->useAttributeAsKey('name')
                                ->prototype('variable')->end()
                            ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    private function addResolversSections(ArrayNodeDefinition $resolversPrototypeNode)
    {
        $this->addConfigurationSections($this->resolversFactories, $resolversPrototypeNode);
    }

    private function addLoadersSections(ArrayNodeDefinition $resolversPrototypeNode)
    {
        $this->addConfigurationSections($this->loadersFactories, $resolversPrototypeNode);
    }

    private function addConfigurationSections(array $factories, ArrayNodeDefinition $definition)
    {
        foreach ($factories as $f) {
            $f->addConfiguration($definition->children()->arrayNode($f->getName()));
        }
    }
}
