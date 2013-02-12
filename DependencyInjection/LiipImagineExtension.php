<?php

namespace Liip\ImagineBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\Kernel;

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

        if ($config['cache_clearer']) {
            $loader->load('cache_clearer.xml');
        }

        $container->setAlias('liip_imagine', new Alias('liip_imagine.'.$config['driver']));

        $cachePrefix = $config['cache_prefix'] ? '/'.trim($config['cache_prefix'], '/') : '';
        $container->setParameter('liip_imagine.cache_prefix', $cachePrefix);
        $container->setParameter('liip_imagine.web_root', $config['web_root']);
        $container->setParameter('liip_imagine.data_root', $config['data_root']);
        $container->setParameter('liip_imagine.cache_mkdir_mode', $config['cache_mkdir_mode']);
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

        if ('2' == Kernel::MAJOR_VERSION && '0' == Kernel::MINOR_VERSION) {
            $container->removeDefinition('liip_imagine.cache.clearer');
        }

        $container->setParameter('liip_imagine.cache.resolver.base_path', $config['cache_base_path']);

        $resources = $container->hasParameter('twig.form.resources') ? $container->getParameter('twig.form.resources') : array();
        $resources[] = 'LiipImagineBundle:Form:form_div_layout.html.twig';
        $container->setParameter('twig.form.resources', $resources);
    }
}
