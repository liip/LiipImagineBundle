<?php
namespace Liip\ImagineBundle\Tests\Functional\Imagine;

use Liip\ImagineBundle\Tests\Functional\WebTestCase;

class SimpleMimeTypeGuesserTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $this->createClient();

        $service = self::$kernel->getContainer()->get('liip_imagine.mime_type_guesser');

        $this->assertInstanceOf('Liip\ImagineBundle\Imagine\SimpleMimeTypeGuesser', $service);
    }
}
