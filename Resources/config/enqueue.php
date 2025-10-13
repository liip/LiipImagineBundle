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

use Enqueue\Client\ProducerInterface;
use Liip\ImagineBundle\Async\ResolveCacheProcessor;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_imagine.async.resolve_cache_processor', ResolveCacheProcessor::class)
            ->public()
            ->tag('enqueue.command_subscriber')
            ->tag('enqueue.transport.processor')
            ->args([
                service('liip_imagine.filter.manager'),
                service('liip_imagine.service.filter'),
                service(ProducerInterface::class),
            ]);
};
