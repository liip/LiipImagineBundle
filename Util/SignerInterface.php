<?php

namespace Liip\ImagineBundle\Util;

interface SignerInterface
{
    /**
     * Return the hash for a string
     *
     * @param string|array $data
     * @return array
     */
    public function getHash($data);

    /**
     * Check a hash is correct for a string
     *
     * @param string|array $data
     * @param string $hash
     * @return boolean
     */
    public function checkHash($data, $hash);
}
