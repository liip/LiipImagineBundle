<?php

namespace Avalanche\Bundle\ImagineBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AvalancheImagineExtension extends Extension
{
    /**
     * @see Symfony\Component\DependencyInjection\Extension.ExtensionInterface::load()
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('imagine.xml');

        $config = $this->mergeConfig($configs);

        $driver = 'gd';

        if (isset($config['driver'])) {
            $driver = strtolower($config['driver']);
        }

        if (!in_array($driver, array('gd', 'imagick'))) {
            throw new \InvalidArgumentException('Invalid imagine driver specified');
        }

        $container->setAlias('imagine', new Alias('imagine.'.$driver));

        foreach (array('cache_prefix', 'web_root', 'filters') as $key) {
            if (isset($config[$key])) {
                $container->setParameter('imagine.'.$key, $config[$key]);
            }
        }
    }

    private function mergeConfig(array $configs)
    {
        $config = array();

        foreach ($configs as $cnf) {
            $config = array_merge($config, $cnf);
        }

        return $config;
    }

    /**
     * @see Symfony\Component\DependencyInjection\Extension.ExtensionInterface::getAlias()
     * @codeCoverageIgnore
     */
    function getAlias()
    {
        return 'avalanche_imagine';
    }
}
