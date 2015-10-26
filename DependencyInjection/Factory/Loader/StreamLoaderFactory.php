<?php

namespace Liip\ImagineBundle\DependencyInjection\Factory\Loader;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class StreamLoaderFactory implements LoaderFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $loaderName, array $config)
    {
        $loaderDefinition = new DefinitionDecorator('liip_imagine.binary.loader.prototype.stream');
        $loaderDefinition->replaceArgument(0, $config['wrapper']);
        $loaderDefinition->replaceArgument(1, $config['context']);
        $loaderDefinition->addTag('liip_imagine.binary.loader', array(
            'loader' => $loaderName,
        ));
        $loaderId = 'liip_imagine.binary.loader.'.$loaderName;

        $container->setDefinition($loaderId, $loaderDefinition);

        return $loaderId;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'stream';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('wrapper')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('context')->defaultValue(null)->end()
            ->end()
        ;
    }
}
