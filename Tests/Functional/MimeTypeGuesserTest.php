<?php

namespace Liip\ImagineBundle\Tests\Functional;

class MimeTypeGuesserTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $this->createClient();
        $guesser = self::$kernel->getContainer()->get('liip_imagine.mime_type_guesser');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser', $guesser);
    }
}
