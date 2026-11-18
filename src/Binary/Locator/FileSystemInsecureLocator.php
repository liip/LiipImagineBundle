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

use Liip\ImagineBundle\Imagine\Cache\Helper\PathHelper;

class FileSystemInsecureLocator extends FileSystemLocator
{
    protected function generateAbsolutePath(string $root, string $path): ?string
    {
        if (!PathHelper::containsTraversal($path)
            && false !== file_exists($absolute = $root.DIRECTORY_SEPARATOR.$path)
        ) {
            return $absolute;
        }

        return null;
    }
}
