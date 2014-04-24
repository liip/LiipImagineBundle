<?php

namespace Liip\ImagineBundle\Util;

interface SignerInterface
{
    /**
     * Return the hash for a string
     *
     * @param  string $path
     * @param  array  $data
     * @param  bool   $trim
     * @return array
     */
    public function getHash($path, array $data, $trim = false);

    /**
     * Check a hash is correct for a string
     *
     * @param  string  $path
     * @param  array   $data
     * @param  string  $hash
     * @return boolean
     */
    public function checkHash($path, array $data, $hash);
}
