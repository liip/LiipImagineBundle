<?php

namespace Liip\ImagineBundle\Tests\Functional\Imagine\Data;

use Liip\ImagineBundle\Tests\Functional\WebTestCase;

class DataManagerTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $this->createClient();
        $service = self::$kernel->getContainer()->get('liip_imagine.data.manager');

        $this->assertInstanceOf('Liip\ImagineBundle\Imagine\Data\DataManager', $service);
    }
}
