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

use Liip\ImagineBundle\Utility\Framework\SymfonyFramework;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class LocatorsCompilerPass extends AbstractCompilerPass
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach (array_keys($container->findTaggedServiceIds('liip_imagine.binary.locator')) as $id) {
            $this->disableSharedDefinition($container->getDefinition($id));
        }
    }

    /**
     * @param Definition $definition
     */
    private function disableSharedDefinition(Definition $definition)
    {
        if (SymfonyFramework::hasDefinitionSharedToggle()) {
            $definition->setShared(false);
        } else {
            $definition->setScope('prototype');
        }
    }
}
