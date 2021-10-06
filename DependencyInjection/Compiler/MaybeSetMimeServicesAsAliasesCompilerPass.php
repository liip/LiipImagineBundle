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

/**
 * Changes the `liip_imagine.mime_type_guesser` and `liip_imagine.extension_guesser` services to be aliases of the
 * `mime_types` service provided by the FrameworkBundle when available.
 *
 * This compiler pass can be removed when dropping support for Symfony 4.2 and earlier.
 *
 * @internal
 */
final class MaybeSetMimeServicesAsAliasesCompilerPass extends AbstractCompilerPass
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('mime_types')) {
            $container->removeDefinition('liip_imagine.mime_type_guesser');
            $container->removeDefinition('liip_imagine.extension_guesser');

            $container->setAlias('liip_imagine.mime_type_guesser', 'mime_types');
            $container->setAlias('liip_imagine.extension_guesser', 'mime_types');

            $message = 'Replaced the "%s" and "%s" service definitions with aliases to "%s"';

            $this->log($container, $message, 'liip_imagine.mime_type_guesser', 'liip_imagine.extension_guesser', 'mime_types');
        }
    }
}
