<?php

namespace Liip\ImagineBundle\Tests\Functional\Imagine\Filter\Loader;

use Liip\ImagineBundle\Tests\Functional\WebTestCase;

class DownscaleFilterLoaderTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $this->createClient();
        $service = self::$kernel->getContainer()->get('liip_imagine.filter.loader.downscale');

        $this->assertInstanceOf('Liip\ImagineBundle\Imagine\Filter\Loader\DownscaleFilterLoader', $service);
    }
}
