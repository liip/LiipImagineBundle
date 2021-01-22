<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\DependencyInjection\Compiler;

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
        $canFiltersStillFunction = $container->hasParameter('kernel.root_dir');
        $throwWarning = function (string $filterName) use ($canFiltersStillFunction) {
            $message = sprintf(
                'The "%s" filter %s in Symfony 5.0. Please use "%s_image" and adapt the "image" option to be relative to the "%%kernel.project_dir%%" instead of "%%kernel.root_dir%%".',
                $filterName,
                $canFiltersStillFunction ? 'is deprecated and will not work' : 'no longer works',
                $filterName
            );

            if ($canFiltersStillFunction) {
                @trigger_error($message, E_USER_DEPRECATED);
            } else {
                throw new \InvalidArgumentException($message);
            }
        };

        $filterSets = $container->getParameter('liip_imagine.filter_sets');
        foreach ($filterSets as $filterSet) {
            foreach ($filterSet['filters'] as $filterName => $filter) {
                if ('paste' === $filterName) {
                    $throwWarning('paste');
                }

                if ('watermark' === $filterName) {
                    $throwWarning('watermark');
                }
            }
        }

        // remove the definitions entirely if kernel.root_dir does not exist
        if (!$canFiltersStillFunction) {
            $container->removeDefinition('liip_imagine.filter.loader.watermark');
            $container->removeDefinition('liip_imagine.filter.loader.paste');
        }
    }
}
