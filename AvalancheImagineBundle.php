<?php

namespace Avalanche\Bundle\ImagineBundle;

use Avalanche\Bundle\ImagineBundle\DependencyInjection\Compiler\FiltersCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AvalancheImagineBundle extends Bundle
{

    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function getPath()
    {
        return strtr(__DIR__, '\\', '/');
    }

    public function registerExtensions(ContainerBuilder $container)
    {
        $container->addCompilerPass(new FiltersCompilerPass());
        parent::registerExtensions($container);
    }
}
