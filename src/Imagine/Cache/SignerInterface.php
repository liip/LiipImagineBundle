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
     */
    public function sign(string $path, ?array $runtimeConfig = null): string;

    /**
     * Check hash is correct.
     */
    public function check(string $hash, string $path, ?array $runtimeConfig = null): bool;
}
