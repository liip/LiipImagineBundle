<?php

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
