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
use Symfony\Component\DependencyInjection\Reference;

class ResolversCompilerPass extends AbstractCompilerPass
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $tags = $container->findTaggedServiceIds('liip_imagine.cache.resolver');

        if (count($tags) > 0 && $container->hasDefinition('liip_imagine.cache.manager')) {
            $manager = $container->getDefinition('liip_imagine.cache.manager');

            foreach ($tags as $id => $tag) {
                $manager->addMethodCall('addResolver', array($tag[0]['resolver'], new Reference($id)));
                $this->log($container, 'Registered imagine-bimdle cache resolver: %s', array($id));
            }
        }
    }
}
