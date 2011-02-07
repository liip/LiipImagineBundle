<?php

namespace Bundle\Avalanche\ImagineBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class ImagineExtension extends Extension
{
    public function loadConfig(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, __DIR__.'../Resources/config');
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

        foreach (array('cache_dir', 'web_root') as $key) {
            if (isset($driver[$key])) {
                $container->setParameter('imagine.public.'.$key, $driver[$key]);
            }
        }
    }
}
