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
    public function getHash($data)
    {
        return urlencode(base64_encode(hash_hmac('sha256', is_array($data) ? serialize($data) : $data, $this->secret, true)));
    }

    /**
     * {@inheritdoc}
     */
    public function checkHash($data, $hash)
    {
        return $this->getHash($data) === $hash;
    }
}