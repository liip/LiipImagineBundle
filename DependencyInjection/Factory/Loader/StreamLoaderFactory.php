<?php
/**
 * Created by PhpStorm.
 * User: makasim
 * Date: 2/21/14
 * Time: 4:44 PM
 */

namespace Liip\ImagineBundle\DependencyInjection\Factory\Loader;


use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class StreamLoaderFactory implements LoaderFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $loaderName, array $config)
    {
        $loaderDefinition = new DefinitionDecorator('liip_imagine.data.loader.prototype.stream');
        $loaderDefinition->replaceArgument(0, $config['wrapper']);
        $loaderDefinition->replaceArgument(1, $config['context']);
        $loaderDefinition->addTag('liip_imagine.data.loader', array(
            'loader' => $loaderName
        ));
        $loaderId = 'liip_imagine.data.loader.'.$loaderName;

        $container->setDefinition($loaderId, $loaderDefinition);

        return $loaderId;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'stream';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('wrapper')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('context')->defaultValue(null)->end()
            ->end()
        ;
    }
}
