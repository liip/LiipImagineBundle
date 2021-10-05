<?php

namespace Liip\ImagineBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CacheWarmersCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $tags = $container->findTaggedServiceIds('liip_imagine.cache.warmer');

        if (count($tags) > 0 && $container->hasDefinition('liip_imagine.cache.warmer')) {
            $warmer = $container->getDefinition('liip_imagine.cache.warmer');

            foreach ($tags as $id => $tag) {
                $warmer->addMethodCall('addWarmer', array($tag[0]['warmer'], new Reference($id)));
            }
        }
    }
}
