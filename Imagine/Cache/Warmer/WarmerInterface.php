<?php

namespace Liip\ImagineBundle\Imagine\Cache\Warmer;

/**
 * Interface WarmerInterface
 * 
 * @author Konstantin Tjuterev <kostik.lv@gmail.com>
 */
interface WarmerInterface
{
    /**
     * Returns an array where each element is an array containing 'path' key, starting from $start up to $chunk total
     *
     * 'path' key of each element is used as a path to resolve and warm
     * all other elements are passed back to setWarmed to allow warmer to store warmed-up state of paths
     *
     * @param int  $start Initial offset
     * @param int  $chunk Chunk size
     *
     * @return array
     */
    function getPaths($start, $chunk);

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
    function setWarmed($data);

    /**
     * Clears warmed-up state of path(s) or all images
     *
     * @param $paths mixed If null - warmed state for all images should be cleared, if string or array - corresponding paths should be cleared
     *
     * @return void
     */
    function clearWarmed($paths = null);
}
 