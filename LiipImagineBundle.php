<?php

namespace Liip\ImagineBundle;

use Liip\ImagineBundle\DependencyInjection\Compiler\LoadersCompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\HttpKernel\Bundle\Bundle;

class LiipImagineBundle extends Bundle
{
    /**
     * @see Symfony\Component\HttpKernel\Bundle.Bundle::build()
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new LoadersCompilerPass());
    }

    /**
     * Returns a cleaned version number
     *
     * @param string $version
     * @return string
     */
    public static function getSymfonyVersion($version)
    {
        return implode('.', array_slice(array_map(function($val)
        {
            return (int)$val;
        }, explode('.', $version)), 0, 3));
    }
}
