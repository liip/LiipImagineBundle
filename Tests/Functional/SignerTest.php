<?php

namespace Liip\ImagineBundle\Tests\Functional;

class SignerTest extends WebTestCase
{
    public function testGetAsService()
    {
        $this->createClient();
        $service = self::$kernel->getContainer()->get('liip_imagine.cache.signer');

        $this->assertInstanceOf('Liip\ImagineBundle\Imagine\Cache\SignerInterface', $service);
    }
}
