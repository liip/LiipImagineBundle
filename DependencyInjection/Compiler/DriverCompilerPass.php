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

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Give a helpful exception message in case the imagine driver does not exist.
 *
 * Third parties can provide a driver, and thus we can only validate after the container has been built.
 */
class DriverCompilerPass extends AbstractCompilerPass
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $liipImagineDriver = $container->getParameter('liip_imagine.driver_service');

        if (!$container->hasDefinition($liipImagineDriver)) {
            throw new InvalidConfigurationException(sprintf("Specified driver '%s' is not defined.", $liipImagineDriver));
        }
    }
}
