<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Bridge\SymfonyUx;

use Symfony\UX\Image\Provider\ProviderInterface;

/**
 * Shared names used for the Symfony UX Image bridge.
 *
 * Declares no symfony/ux-image type, so that it is loadable even when the Symfony UX image component is absent.
 */
final class UxImage
{
    /**
     * The DSN scheme, and the key for this provider in its "operations" map.
     */
    public const PROVIDER = 'liipimagine';

    /**
     * The filter set {@see ImageProvider} merges its runtime configurations onto.
     */
    public const FILTER_SET = 'ux_image';

    /**
     * The formats the bundle declares a filter set variant for, and the default of the DSN "formats" option.
     */
    public const FORMATS = ['webp', 'jpeg', 'png'];

    /**
     * The filter set that renders $filterSet in $format.
     *
     * LiipImagineBundle does not support format selection in the query string, so we need a distinct filter set per
     * image format.
     */
    public static function filterSet(string $filterSet, ?string $format): string
    {
        return null === $format ? $filterSet : $filterSet.'_'.$format;
    }

    public static function isInstalled(): bool
    {
        return interface_exists(ProviderInterface::class);
    }

    /**
     * The filter sets the bundle needs to declare to make the provider work.
     *
     * If Symfony UX image is not installed, the set is empty.
     *
     * @return array<string, string|null> filter set name => the format it encodes to
     */
    public static function declaredFilterSets(): array
    {
        if (!self::isInstalled()) {
            return [];
        }

        $filterSets = [self::FILTER_SET => null];

        foreach (self::FORMATS as $format) {
            $filterSets[self::filterSet(self::FILTER_SET, $format)] = $format;
        }

        return $filterSets;
    }
}
