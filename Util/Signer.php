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
    public function getHash($path, array $data)
    {
        return urlencode(base64_encode(hash_hmac('sha256', ltrim($path, '/') . serialize($data), $this->secret, true)));
    }

    /**
     * {@inheritdoc}
     */
    public function checkHash($path, array $data, $hash)
    {
        return $this->getHash($path, $data) === $hash;
    }
}
