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

use Symfony\Component\DependencyInjection\ContainerBuilder;

class LocatorsCompilerPass extends AbstractCompilerPass
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('liip_imagine.binary.locator') as $id => $tags) {
            if (isset($tags[0]['shared'])) {
                $this->setDefinitionSharing($container->getDefinition($id), (bool) $tags[0]['shared']);
            }
        }
    }
}
