<?php

namespace Avalanche\Bundle\ImagineBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class LoadersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $tags = $container->findTaggedServiceIds('imagine.filter.loader');

        if (count($tags) > 0 && $container->hasDefinition('imagine.filter.manager')) {
            $manager = $container->getDefinition('imagine.filter.manager');

            foreach ($tags as $id => $tag) {
                $manager->addMethodCall('addLoader', array($tag[0]['filter'], new Reference($id)));
            }
        }
    }
}
