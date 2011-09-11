<?php

namespace Avalanche\Bundle\ImagineBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Reference;

class AvalancheImagineExtension extends Extension
{
    /**
     * @see Symfony\Component\DependencyInjection\Extension.ExtensionInterface::load()
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('imagine.xml');

        $container->setAlias('imagine', new Alias('imagine.'.$config['driver']));
        foreach (array('cache_prefix', 'web_root', 'formats', 'cache', 'filters') as $key) {
            $container->setParameter('imagine.'.$key, $config[$key]);
        }

        if ($container->getParameter('imagine.cache')) {
            $controller = $container->getDefinition('imagine.controller');
            $controller->addArgument(new Reference('imagine.cache.path.resolver'));
        }

        if (!empty($config['loader'])) {
            $controller = $container->getDefinition('imagine.controller');
            $controller->replaceArgument(0, new Reference($config['loader']));
        }
    }
}
