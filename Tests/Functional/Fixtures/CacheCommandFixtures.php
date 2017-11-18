<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Functional\Fixtures;

class CacheCommandFixtures
{
    /**
     * @return string[]
     */
    public static function getAvailableFilterAndImageCombinations()
    {
        $data = array();

        foreach (static::getCascadingArray(static::getValidImages()) as $images) {
            foreach (static::getCascadingArray(static::getValidFilters()) as $filters) {
                $data[] = array($images, $filters);
            }
        }

        return $data;
    }

    /**
     * @return string[]
     */
    public static function getValidFilters()
    {
        return array('profile_thumb_sm', 'profile_thumb_lg', 'profile_main');
    }

    /**
     * @return string[]
     */
    public static function getValidImages()
    {
        return array('images/cats.jpeg', 'cats-foo-bundle.jpg', 'cats-bar-bundle.jpg');
    }

    /**
     * @param array $notOneOf
     *
     * @return null|string[]
     */
    public static function getFiltersNotInArray(array $notOneOf)
    {
        return array_diff(static::getValidFilters(), $notOneOf) ?: null;
    }

    /**
     * @param array $notOneOf
     *
     * @return null|string[]
     */
    public static function getImagesNotInArray(array $notOneOf)
    {
        return array_diff(static::getValidImages(), $notOneOf) ?: null;
    }

    /**
     * @return string[]
     */
    public static function getInvalidImages()
    {
        return array('foo/does-not-exist.jpg', 'bar/does-not-exist.jpeg', 'baz/does-not-exist.png');
    }

    /**
     * @return string[]
     */
    public static function getInvalidFilters()
    {
        return array('invalid_filter_foo', 'invalid_filter_bar', 'invalid_filter_baz');
    }

    /**
     * @param array $array
     *
     * @return string[]
     */
    private static function getCascadingArray(array $array)
    {
        $data = array();

        foreach ($array as $a) {
            $data[] = array_merge(0 === count($data) ? array() : $data[count($data) - 1], array($a));
        }

        return $data;
    }
}
