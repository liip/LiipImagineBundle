<?php

namespace Liip\ImagineBundle\Tests\Functional\Imagine\Filter\Loader;

use Liip\ImagineBundle\Tests\Functional\WebTestCase;

/**
 * Functional test cases for RotateFilterLoader class.
 *
 * @author Bocharsky Victor <bocharsky.bw@gmail.com>
 */
class RotateFilterLoaderTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $this->createClient();
        $service = self::$kernel->getContainer()->get('liip_imagine.filter.loader.rotate');

        $this->assertInstanceOf('Liip\ImagineBundle\Imagine\Filter\Loader\RotateFilterLoader', $service);
    }
}
