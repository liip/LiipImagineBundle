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

use Liip\ImagineBundle\Templating\Helper\FilterHelper;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_imagine.templating.filter_helper', FilterHelper::class)
            ->tag('templating.helper', ['alias' => 'imagine'])
            ->args([
                service('liip_imagine.cache.manager'),
            ])
            ->deprecate('liip/imagine-bundle', '2.2', 'The "%service_id%" service is deprecated since LiipImagineBundle 2.2 and will be removed in 3.0.');
};
