<?php

namespace Liip\ImagineBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class LoadersCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $tags = $container->findTaggedServiceIds('liip_imagine.binary.loader');

        if (count($tags) > 0 && $container->hasDefinition('liip_imagine.data.manager')) {
            $manager = $container->getDefinition('liip_imagine.data.manager');

            foreach ($tags as $id => $tag) {
                $manager->addMethodCall('addLoader', array($tag[0]['loader'], new Reference($id)));
            }
        }
    }
}
