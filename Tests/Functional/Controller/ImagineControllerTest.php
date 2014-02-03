<?php
namespace Liip\ImagineBundle\Tests\Functional\Controller;

use Liip\ImagineBundle\Tests\Functional\WebTestCase;

/**
 * @covers Liip\ImagineBundle\Controller\ImagineController
 */
class ImagineControllerTest extends WebTestCase
{
    public function testCouldBeGetFromContainer()
    {
        $this->createClient();

        $controller = self::$kernel->getContainer()->get('liip_imagine.controller');

        $this->assertInstanceOf('Liip\ImagineBundle\Controller\ImagineController', $controller);
    }
}
