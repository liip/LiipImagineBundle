<?php

namespace Liip\ImagineBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class LoadersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $tags = $container->findTaggedServiceIds('liip_imagine.filter.loader');

        if (count($tags) > 0 && $container->hasDefinition('liip_imagine.filter.manager')) {
            $manager = $container->getDefinition('liip_imagine.filter.manager');

            foreach ($tags as $id => $tag) {
                $manager->addMethodCall('addLoader', array($tag[0]['loader'], new Reference($id)));
            }
        }

        $tags = $container->findTaggedServiceIds('liip_imagine.data.loader');

        if (count($tags) > 0 && $container->hasDefinition('liip_imagine.data.manager')) {
            $manager = $container->getDefinition('liip_imagine.data.manager');

            foreach ($tags as $id => $tag) {
                $manager->addMethodCall('addLoader', array($tag[0]['loader'], new Reference($id)));
            }
        }

        $tags = $container->findTaggedServiceIds('liip_imagine.cache.resolver');

        if (count($tags) > 0 && $container->hasDefinition('liip_imagine.cache.manager')) {
            $manager = $container->getDefinition('liip_imagine.cache.manager');

            foreach ($tags as $id => $tag) {
                $manager->addMethodCall('addResolver', array($tag[0]['resolver'], new Reference($id)));
            }
        }
    }
}
