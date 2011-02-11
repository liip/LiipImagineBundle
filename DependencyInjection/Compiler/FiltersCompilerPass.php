<?php

namespace Avalanche\Bundle\ImagineBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class FiltersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $filters = array();
        foreach ($container->findTaggedServiceIds('imagine.filter') as $id => $tag) {
            $filters[$tag[0]['filter']] = $id;
        }
        $container->setParameter('imagine.filters', $filters);
    }
}
