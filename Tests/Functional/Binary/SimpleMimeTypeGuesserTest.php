<?php

namespace Liip\ImagineBundle\Tests\Functional\Binary;

use Liip\ImagineBundle\Tests\Functional\WebTestCase;

class SimpleMimeTypeGuesserTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $this->createClient();

        $service = self::$kernel->getContainer()->get('liip_imagine.binary.mime_type_guesser');

        $this->assertInstanceOf('Liip\ImagineBundle\Binary\SimpleMimeTypeGuesser', $service);
    }
}
