<?php

namespace Liip\ImagineBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ResolversCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $tags = $container->findTaggedServiceIds('liip_imagine.cache.resolver');

        if (count($tags) > 0 && $container->hasDefinition('liip_imagine.cache.manager')) {
            $manager = $container->getDefinition('liip_imagine.cache.manager');

            foreach ($tags as $id => $tag) {
                $manager->addMethodCall('addResolver', array($tag[0]['resolver'], new Reference($id)));
            }
        }
    }
}
