<?php

namespace Liip\ImagineBundle\Tests\Functional\Imagine\Filter\Loader;

use Liip\ImagineBundle\Tests\Functional\WebTestCase;

/**
 * Functional test cases for GrayscaleFilterLoader class.
 *
 * @author Gregoire Humeau <gregoire.humeau@gmail.com>
 */
class GrayscaleFilterLoaderTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $this->createClient();
        $service = self::$kernel->getContainer()->get('liip_imagine.filter.loader.grayscale');

        $this->assertInstanceOf('Liip\ImagineBundle\Imagine\Filter\Loader\GrayscaleFilterLoader', $service);
    }
}
