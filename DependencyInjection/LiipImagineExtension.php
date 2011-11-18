<?php

namespace Liip\ImagineBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Reference;

class LiipImagineExtension extends Extension
{
    /**
     * @see Symfony\Component\DependencyInjection\Extension.ExtensionInterface::load()
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('imagine.xml');

        $container->setAlias('liip_imagine', new Alias('liip_imagine.'.$config['driver']));

        $cachePrefix = $config['cache_prefix'] ? '/'.trim($config['cache_prefix'], '/') : '';
        $container->setParameter('liip_imagine.cache_prefix', $cachePrefix);
        $container->setParameter('liip_imagine.web_root', $config['web_root']);
        $container->setParameter('liip_imagine.formats', $config['formats']);
        $container->setParameter('liip_imagine.cache.resolver.default', $config['cache']);

        foreach ($config['filter_sets'] as $filter => $options) {
            if (isset($options['path'])) {
                $config['filter_sets'][$filter]['path'] = '/'.trim($options['path'], '/');
            }
        }
        $container->setParameter('liip_imagine.filter_sets', $config['filter_sets']);

        $container->setParameter('liip_imagine.data.loader.default', $config['data_loader']);

        $container->setParameter('liip_imagine.controller_action', $config['controller_action']);
    }
}
