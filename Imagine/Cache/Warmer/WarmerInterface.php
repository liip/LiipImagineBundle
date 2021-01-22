<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Cache\Warmer;

interface WarmerInterface
{
    /**
     * Returns an array where each element is an array containing 'path' key, starting from $start up to $chunk total
     *
     * 'path' key of each element is used as a path to resolve and warm
     * all other elements are passed back to setWarmed to allow warmer to store warmed-up state of paths
     *
     * @param int $start Initial offset
     * @param int $chunk Chunk size
     *
     * @return array
     */
    public function getPaths($start, $chunk);

    /**
     * The return value of getPaths is passed back to this method to allow saving warmed state
     *
     * The method is called once for every chunk processed during warmup
     * Warmers should implement this method in order to store warmed state and then use it
     * in getPaths to avoid returning already warmed paths
     *
     * @param $data
     *
     * @return void
     */
    public function setWarmed($data);

    /**
     * Clears warmed-up state of path(s) or all images
     *
     * @param $paths mixed If null - warmed state for all images should be cleared, if string or array - corresponding paths should be cleared
     *
     * @return void
     */
    public function clearWarmed($paths = null);
}
