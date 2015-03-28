<?php

namespace Liip\ImagineBundle\Tests\Functional\Imagine\Filter;

use Liip\ImagineBundle\Tests\Functional\WebTestCase;

class FilterManagerTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $this->createClient();
        $service = self::$kernel->getContainer()->get('liip_imagine.filter.manager');

        $this->assertInstanceOf('Liip\ImagineBundle\Imagine\Filter\FilterManager', $service);
    }
}
