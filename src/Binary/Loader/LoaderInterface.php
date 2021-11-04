<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Binary\Loader;

use Liip\ImagineBundle\Binary\BinaryInterface;

interface LoaderInterface
{
    /**
     * Retrieve the Image represented by the given path.
     *
     * The path may be a file path on a filesystem, or any unique identifier among the storage engine implemented by this Loader.
     *
     * @param mixed $path
     *
     * @return BinaryInterface|string An image binary content
     */
    public function find($path);
}
