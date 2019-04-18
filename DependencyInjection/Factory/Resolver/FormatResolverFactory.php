<?php

namespace Liip\ImagineBundle\DependencyInjection\Factory\Resolver;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * Class FormatResolverFactory
 *
 * @copyright 2017 IntechSystems, SIA
 * @package   Liip\ImagineBundle\DependencyInjection\Factory\Resolver
 * @author    Mihail Savluga
 */
class FormatResolverFactory extends WebPathResolverFactory
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $resolverName, array $config)
    {
        $resolverDefinition = new DefinitionDecorator('liip_imagine.cache.resolver.prototype.format');
        $resolverDefinition->replaceArgument(2, $config['web_root']);
        $resolverDefinition->replaceArgument(3, $config['cache_prefix']);
        $resolverDefinition->addTag('liip_imagine.cache.resolver', array(
            'resolver' => $resolverName
        ));
        $resolverId = 'liip_imagine.cache.resolver.'.$resolverName;

        $container->setDefinition($resolverId, $resolverDefinition);

        return $resolverId;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'format';
    }
}
