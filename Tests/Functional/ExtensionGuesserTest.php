<?php

namespace Liip\ImagineBundle\Tests\Functional;

class ExtensionGuesserTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $this->createClient();
        $guesser = self::$kernel->getContainer()->get('liip_imagine.extension_guesser');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser', $guesser);
    }
}
