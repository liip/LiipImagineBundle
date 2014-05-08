<?php

namespace Liip\ImagineBundle\Imagine\Cache;

interface SignerInterface
{
    /**
     * Return the hash for path and data
     *
     * @param  string $path
     * @param  array  $data
     * @return string
     */
    public function getHash($path, array $data);

    /**
     * Trim the hash
     *
     * @param  string $hash
     * @return string
     */
    public function trimHash($hash);
}
