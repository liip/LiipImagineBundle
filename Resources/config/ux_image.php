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

use Liip\ImagineBundle\Bridge\SymfonyUx\ImageProviderFactory;
use Liip\ImagineBundle\Bridge\SymfonyUx\UxImage;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_imagine.bridge.symfony_ux.image_provider_factory', ImageProviderFactory::class)
            ->tag('ux_image.provider_factory', ['provider' => UxImage::PROVIDER])
            ->args([
                service('liip_imagine.cache.manager'),
                service('liip_imagine.filter.configuration'),
                UxImage::FILTER_SET,
                param('liip_imagine.webp.generate'),
            ]);
};
