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
    protected $resolversFactories = array();

    /**
     * @var LoaderFactoryInterface[]
     */
    protected $loadersFactories = array();

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
     * @see Symfony\Component\DependencyInjection\Extension.ExtensionInterface::load()
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

        $this->setFactories($container);

        if (interface_exists('Imagine\Image\Metadata\MetadataReaderInterface')) {
            $container->getDefinition('liip_imagine.'.$config['driver'])->addMethodCall('setMetadataReader', array(new Reference('liip_imagine.meta_data.reader')));
        } else {
            $container->removeDefinition('liip_imagine.meta_data.reader');
        }

        $container->setAlias('liip_imagine', new Alias('liip_imagine.'.$config['driver']));

        $container->setParameter('liip_imagine.cache.resolver.default', $config['cache']);

        $container->setParameter('liip_imagine.default_image', $config['default_image']);

        $container->setParameter('liip_imagine.filter_sets', $config['filter_sets']);
        $container->setParameter('liip_imagine.binary.loader.default', $config['data_loader']);

        $container->setParameter('liip_imagine.controller.filter_action', $config['controller']['filter_action']);
        $container->setParameter('liip_imagine.controller.filter_runtime_action', $config['controller']['filter_runtime_action']);

        $resources = $container->hasParameter('twig.form.resources') ? $container->getParameter('twig.form.resources') : array();
        $resources[] = 'LiipImagineBundle:Form:form_div_layout.html.twig';
        $container->setParameter('twig.form.resources', $resources);
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     */
    protected function loadResolvers(array $config, ContainerBuilder $container)
    {
        foreach ($config as $resolverName => $resolverConfig) {
            $factoryName = key($resolverConfig);
            $factory = $this->resolversFactories[$factoryName];

            $factory->create($container, $resolverName, $resolverConfig[$factoryName]);
        }
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     */
    protected function loadLoaders(array $config, ContainerBuilder $container)
    {
        foreach ($config as $loaderName => $loaderConfig) {
            $factoryName = key($loaderConfig);
            $factory = $this->loadersFactories[$factoryName];

            $factory->create($container, $loaderName, $loaderConfig[$factoryName]);
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    private function setFactories(ContainerBuilder $container)
    {
        $factories = array(
            'liip_imagine.mime_type_guesser' => array('Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser', 'getInstance'),
            'liip_imagine.extension_guesser' => array('Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser', 'getInstance'),
        );

        foreach ($factories as $service => $factory) {
            $definition = $container->getDefinition($service);
            if (method_exists($definition, 'setFactory')) {
                // to be inlined in imagine.xml when dependency on Symfony DependencyInjection is bumped to 2.6
                $definition->setFactory($factory);
            } else {
                // to be removed when dependency on Symfony DependencyInjection is bumped to 2.6
                $definition->setFactoryClass($factory[0]);
                $definition->setFactoryMethod($factory[1]);
            }
        }
    }
}
