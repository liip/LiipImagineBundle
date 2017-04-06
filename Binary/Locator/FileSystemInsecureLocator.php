<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Binary\Locator;

class FileSystemInsecureLocator extends FileSystemLocator
{
    /**
     * @param string $root
     * @param string $path
     *
     * @return string|false
     */
    protected function generateAbsolutePath($root, $path)
    {
        if (false !== strpos($path, '..'.DIRECTORY_SEPARATOR) ||
            false !== strpos($path, DIRECTORY_SEPARATOR.'..') ||
            false === file_exists($absolute = $root.DIRECTORY_SEPARATOR.$path)) {
            return false;
        }

        return $absolute;
    }
}
