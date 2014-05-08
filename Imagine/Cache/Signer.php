<?php

namespace Liip\ImagineBundle\Imagine\Cache;

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
    public function sign($path, array $runtimeConfig = null)
    {
        return preg_replace('/[^a-zA-Z0-9-_]/', '', base64_encode(hash_hmac('sha256', ltrim($path, '/') . (null === $runtimeConfig ?: serialize($runtimeConfig)), $this->secret, true)));
    }

    /**
     * {@inheritdoc}
     */
    public function check($hash, $path, array $runtimeConfig = null)
    {
        return $hash === $this->sign($path, $runtimeConfig);
    }
}
