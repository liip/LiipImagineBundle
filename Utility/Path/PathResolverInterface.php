<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Utility\Path;

interface PathResolverInterface
{
    /**
     * @param string $path
     * @param string $filter
     *
     * @return string
     */
    public function getFilePath($path, $filter);
    
    /**
     * @param string $path
     * @param string $filter
     *
     * @return string
     */
    public function getFileUrl($path, $filter);
    
    /**
     * @return string
     */
    public function getCacheRoot();
}
