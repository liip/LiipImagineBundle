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
        $container->setParameter('liip_imagine.cache', $config['cache']);
        foreach ($config['filters'] as $filter => $options) {
            if (isset($options['path'])) {
                $config['filters'][$filter]['path'] = '/'.trim($options['path'], '/');
            }
        }
        $container->setParameter('liip_imagine.filters', $config['filters']);

        if ($container->getParameter('liip_imagine.cache')) {
            $controller = $container->getDefinition('liip_imagine.controller');
            $controller->addArgument(new Reference('liip_imagine.cache.path.resolver'));
        }

        if (!empty($config['loader'])) {
            $controller = $container->getDefinition('liip_imagine.controller');
            $controller->replaceArgument(0, new Reference($config['loader']));
        }
    }
}
