<?php

namespace Liip\ImagineBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CreateCacheDirectoriesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->getParameter('liip_imagine.cache')) {
            return;
        }

        $webRoot     = $container->getParameter('liip_imagine.web_root');
        $cachePrefix = $container->getParameter('liip_imagine.cache_prefix');
        $filters     = $container->getParameter('liip_imagine.filters');
        $mode        = $container->getParameter('liip_imagine.cache_mkdir_mode');

        foreach ($filters as $filter => $options) {
            $dir = isset($options['path'])
                ? $webRoot.$options['path']
                : $webRoot.$cachePrefix.'/'.$filter;

            if (!is_dir($dir) && !mkdir($dir, $mode, true)) {
                throw new \RuntimeException(sprintf(
                    'Could not create directory for caching processed '.
                    'images in "%s"', $dir
                ));
            }
        }
    }
}
