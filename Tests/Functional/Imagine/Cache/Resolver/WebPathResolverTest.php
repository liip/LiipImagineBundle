<?php

namespace Liip\ImagineBundle\Tests\Functional\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Tests\Functional\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver
 */
class WebPathResolverTest extends WebTestCase
{
    public function testCouldBeGetFromContainer()
    {
        $this->createClient();

        $resolver = self::$kernel->getContainer()->get('liip_imagine.cache.resolver.web_path');

        $this->assertInstanceOf('Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver', $resolver);
    }
}
