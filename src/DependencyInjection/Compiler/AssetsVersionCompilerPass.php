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

use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Inject the Symfony framework assets version parameter to the
 * LiipImagineBundle twig extension if possible.
 *
 * We extract the version parameter from the StaticVersionStrategy service
 * definition. If anything is not as expected, we log a warning and do nothing.
 *
 * The expectation is for the user to configure the assets version in liip
 * imagine for custom setups.
 *
 * Anything other than StaticVersionStrategy needs to be implemented by the
 * user in CacheResolveEvent event listeners.
 */
class AssetsVersionCompilerPass extends AbstractCompilerPass
{
    public function process(ContainerBuilder $container)
    {
        if (!class_exists(StaticVersionStrategy::class)
            // this application has no asset version configured
            || !$container->has('assets._version__default')
            // we are not using the new LazyFilterRuntime
            || !$container->hasDefinition('liip_imagine.templating.filter_runtime')
        ) {
            return;
        }
        $runtimeDefinition = $container->getDefinition('liip_imagine.templating.filter_runtime');
        if (null !== $runtimeDefinition->getArgument(1)) {
            // the asset version has been set explicitly
            return;
        }

        $versionStrategyDefinition = $container->findDefinition('assets._version__default');
        if (!is_a($versionStrategyDefinition->getClass(), StaticVersionStrategy::class, true)) {
            $this->log($container, 'Symfony assets versioning strategy "'.$versionStrategyDefinition->getClass().'" not automatically supported. Configure liip_imagine.twig.assets_version if you have problems with assets versioning');

            return;
        }
        $version = $versionStrategyDefinition->getArgument(0);
        $format = $versionStrategyDefinition->getArgument(1);
        $format = $container->resolveEnvPlaceholders($format);
        if ($format && !$this->str_ends_with($format, '?%%s')) {
            $this->log($container, 'Can not handle assets versioning with custom format "'.$format.'". asset twig function can likely not be used with the imagine_filter');

            return;
        }

        $runtimeDefinition->setArgument(1, $version);
    }

    /**
     * Can be replaced with the built-in method when dropping support for PHP < 8.0
     */
    private function str_ends_with(string $haystack, string $needle): bool
    {
        return mb_substr($haystack, -mb_strlen($needle)) === $needle;
    }
}
