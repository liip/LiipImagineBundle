<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Liip\ImagineBundle\Command\RemoveCacheCommand;
use Liip\ImagineBundle\Command\ResolveCacheCommand;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_imagine.command.cache_remove', RemoveCacheCommand::class)
            ->tag('console.command', ['command' => 'liip:imagine:cache:remove', 'alias' => 'imagine:del'])
            ->args([
                service('liip_imagine.cache.manager'),
                service('liip_imagine.filter.manager'),
                service('liip_imagine.service.filter'),
            ])

        ->set('liip_imagine.command.cache_resolve', ResolveCacheCommand::class)
            ->tag('console.command', ['command' => 'liip:imagine:cache:resolve', 'alias' => 'imagine:get'])
            ->args([
                service('liip_imagine.cache.manager'),
                service('liip_imagine.filter.manager'),
                service('liip_imagine.service.filter'),
            ]);
};
