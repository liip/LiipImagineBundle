<?php

declare(strict_types=1);

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Cache\Helper;

/**
 * @author  Dmitrijs Balabka <dmitry.balabka@gmail.com>
 */
class PathHelper
{
    public static function filePathToUrlPath(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }

    public static function urlPathToFilePath(string $url): string
    {
        // used urldecode instead of rawurlencode for BC safety to support "+" in URL
        return urldecode($url);
    }
}
