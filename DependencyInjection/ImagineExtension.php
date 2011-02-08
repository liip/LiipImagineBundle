<?php

namespace Bundle\Avalanche\ImagineBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class ImagineExtension extends Extension
{
    public function configLoad(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, __DIR__.'/../Resources/config');
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

    private function mergeConfig(array $configs)
    {
        $config = array();

        foreach ($configs as $cnf) {
            $config = array_merge($config, $cnf);
        }

        return $config;
    }

    /**
     * Returns the namespace to be used for this extension (XML namespace).
     *
     * @return string The XML namespace
     */
    function getNamespace()
    {
        return 'http://xmlns.avalanche123.com/dic/imagine';
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    /**
     * Returns the recommended alias to use in XML.
     *
     * This alias is also the mandatory prefix to use when using YAML.
     *
     * @return string The alias
     */
    function getAlias()
    {
        return 'imagine';
    }
}
