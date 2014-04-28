<?php

namespace Liip\ImagineBundle\Util;

class Signer implements SignerInterface
{
    /**
     * @var string
     */
    private $secret;

    /**
     * Constructor
     *
     * @param string $secret
     */
    public function __construct($secret)
    {
        $this->secret = $secret;
    }

    /**
     * {@inheritdoc}
     */
    public function getHash($path, array $data, $trim = false)
    {
        $hash = urlencode(base64_encode(hash_hmac('sha256', ltrim($path, '/') . serialize($data), $this->secret, true)));

        if (true === (bool) $trim) {
            // Strip out characters which may cause issues for some filesystems
            return substr(preg_replace('/[^a-zA-Z0-9-_]/', '', $hash), 0, 8);
        }

        return $hash;
    }

    /**
     * {@inheritdoc}
     */
    public function checkHash($path, array $data, $hash)
    {
        return $this->getHash($path, $data) === $hash;
    }
}