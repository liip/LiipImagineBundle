<?php

namespace Liip\ImagineBundle\DependencyInjection\Factory\Loader;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class FlysystemLoaderFactory implements LoaderFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $loaderName, array $config)
    {
        $loaderDefinition = new DefinitionDecorator('liip_imagine.binary.loader.prototype.flysystem');
        $loaderDefinition->replaceArgument(1, new Reference($config['filesystem_service']));
        $loaderDefinition->addTag('liip_imagine.binary.loader', array(
            'loader' => $loaderName,
        ));
        $loaderId = 'liip_imagine.binary.loader.'.$loaderName;

        $container->setDefinition($loaderId, $loaderDefinition);

        return $loaderId;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'flysystem';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('filesystem_service')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;
    }
}
