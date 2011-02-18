<?php

namespace Avalanche\Bundle\ImagineBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CreateCacheDirectoriesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $webRoot     = $container->getParameter('imagine.web_root');
        $cachePrefix = $container->getParameter('imagine.cache_prefix');
        $filters     = $container->getParameter('imagine.filters');

        foreach ($filters as $filter => $options) {
            if (isset($options['path'])) {
                $dir = $webRoot.'/'.$options['path'];
            } else {
                $dir = $webRoot.'/'.$cachePrefix.'/'.$filter;
            }

            if (!is_dir($dir)) {
                if (!mkdir($dir, 0777, true)) {
                    throw new \RuntimeException(sprintf(
                        'Could not create directory for caching processed '.
                        'images in "%s"', $dir
                    ));
                }
            }
        }
    }
}
