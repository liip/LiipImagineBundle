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

use Liip\ImagineBundle\DependencyInjection\Factory\Loader\LoaderFactoryInterface;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\ResolverFactoryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class LiipImagineExtension extends Extension
{
    /**
     * @var ResolverFactoryInterface[]
     */
    private $resolversFactories = [];

    /**
     * @var LoaderFactoryInterface[]
     */
    private $loadersFactories = [];

    /**
     * @param ResolverFactoryInterface $resolverFactory
     */
    public function addResolverFactory(ResolverFactoryInterface $resolverFactory)
    {
        $this->resolversFactories[$resolverFactory->getName()] = $resolverFactory;
    }

    /**
     * @param LoaderFactoryInterface $loaderFactory
     */
    public function addLoaderFactory(LoaderFactoryInterface $loaderFactory)
    {
        $this->loadersFactories[$loaderFactory->getName()] = $loaderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($this->resolversFactories, $this->loadersFactories);
    }

    /**
     * @see \Symfony\Component\DependencyInjection\Extension.ExtensionInterface::load()
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(
            $this->getConfiguration($configs, $container),
            $configs
        );

        $this->loadResolvers($config['resolvers'], $container);
        $this->loadLoaders($config['loaders'], $container);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('imagine.xml');

        if ($config['enqueue']) {
            $loader->load('enqueue.xml');
        }

        $container->getDefinition('liip_imagine.'.$config['driver'])->addMethodCall('setMetadataReader', [
            new Reference('liip_imagine.meta_data.reader'),
        ]);

        $container->setAlias('liip_imagine', new Alias('liip_imagine.'.$config['driver']));

        $container->setParameter('liip_imagine.cache.resolver.default', $config['cache']);

        $container->setParameter('liip_imagine.default_image', $config['default_image']);

        $container->setParameter('liip_imagine.filter_sets', $config['filter_sets']);
        $container->setParameter('liip_imagine.binary.loader.default', $config['data_loader']);

        $container->setParameter('liip_imagine.controller.filter_action', $config['controller']['filter_action']);
        $container->setParameter('liip_imagine.controller.filter_runtime_action', $config['controller']['filter_runtime_action']);

        $container->setParameter('twig.form.resources', array_merge(
            $container->hasParameter('twig.form.resources') ? $container->getParameter('twig.form.resources') : [],
            ['@LiipImagine/Form/form_div_layout.html.twig']
        ));
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function loadResolvers(array $config, ContainerBuilder $container)
    {
        $this->createFactories($this->resolversFactories, $config, $container);
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function loadLoaders(array $config, ContainerBuilder $container)
    {
        $this->createFactories($this->loadersFactories, $config, $container);
    }

    /**
     * @param array            $factories
     * @param array            $configurations
     * @param ContainerBuilder $container
     */
    private function createFactories(array $factories, array $configurations, ContainerBuilder $container)
    {
        foreach ($configurations as $name => $conf) {
            $factories[key($conf)]->create($container, $name, $conf[key($conf)]);
        }
    }
}
