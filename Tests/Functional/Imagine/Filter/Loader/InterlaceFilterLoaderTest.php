<?php

namespace Liip\ImagineBundle\Tests\Functional\Imagine\Filter\Loader;

use Liip\ImagineBundle\Tests\Functional\WebTestCase;

class InterlaceFilterLoaderTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $this->createClient();
        $service = self::$kernel->getContainer()->get('liip_imagine.filter.loader.interlace');

        $this->assertInstanceOf('Liip\ImagineBundle\Imagine\Filter\Loader\InterlaceFilterLoader', $service);
    }
}
