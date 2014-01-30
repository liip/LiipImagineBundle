<?php
namespace Liip\ImagineBundle\Tests\Functional;

class UriSignerTest extends WebTestCase
{
    public function testGetAsService()
    {
        $this->createClient();
        $service = self::$kernel->getContainer()->get('liip_imagine.uri_signer');

        $this->assertInstanceOf('Symfony\Component\HttpKernel\UriSigner', $service);
    }
} 