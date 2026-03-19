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

use Liip\ImagineBundle\Message\Handler\WarmupCacheHandler;
use Liip\ImagineBundle\Message\WarmupCache;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_imagine.messenger.warmup_cache_processor', WarmupCacheHandler::class)
            ->tag('messenger.message_handler', ['handles' => WarmupCache::class])
            ->args([
                service('liip_imagine.filter.manager'),
                service('liip_imagine.service.filter'),
            ]);
};
