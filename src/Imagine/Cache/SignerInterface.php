<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Cache;

interface SignerInterface
{
    /**
     * Return the hash for path and runtime config.
     *
     * @param string $path
     * @param array  $runtimeConfig
     *
     * @return string
     */
    public function sign($path, array $runtimeConfig = null);

    /**
     * Check hash is correct.
     *
     * @param string $hash
     * @param string $path
     * @param array  $runtimeConfig
     *
     * @return bool
     */
    public function check($hash, $path, array $runtimeConfig = null);
}
