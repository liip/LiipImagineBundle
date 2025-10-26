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

use Liip\ImagineBundle\Templating\FilterExtension;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Templating helpers and extensions
    $services->set('liip_imagine.templating.filter_extension', FilterExtension::class)
        ->private()
        ->args([service('liip_imagine.cache.manager')])
        ->tag('twig.extension');
};
