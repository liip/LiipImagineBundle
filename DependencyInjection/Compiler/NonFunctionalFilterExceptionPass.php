<?php

namespace Liip\ImagineBundle\DependencyInjection\Compiler;

use Liip\ImagineBundle\Imagine\Filter\Loader\NonFunctionalPasteFilterLoader;
use Liip\ImagineBundle\Imagine\Filter\Loader\NonFunctionalWatermarkFilterLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * For transitioning from Symfony 4 to Symfony 5 with the removal
 * of the kernel.root_dir parameter.
 */
class NonFunctionalFilterExceptionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // only needed if kernel.root_dir is no longer present
        if ($container->hasParameter('kernel.root_dir')) {
            return;
        }

        $filterSets = $container->getParameter('liip_imagine.filter_sets');
        foreach ($filterSets as $filterSet) {
            foreach ($filterSet['filters'] as $filterName => $filter) {
                if ($filter === 'paste') {
                    throw new \InvalidArgumentException(sprintf('The "paste" filter no longer works in Symfony 5.0. Please use "paste_image" and adapt the "image" to be relative to the "%kernel.project_dir%" instead of "%kernel.root_dir%".'));
                }

                if ($filter === 'watermark') {
                    throw new \InvalidArgumentException(sprintf('The "paste" filter no longer works in Symfony 5.0. Please use "paste_image" and adapt the "image" to be relative to the "%kernel.project_dir%" instead of "%kernel.root_dir%".'));
                }
            }
        }
    }
}