<?php

namespace Liip\ImagineBundle\Tests\Functional\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Tests\Functional\WebTestCase;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\NoCacheWebPathResolver
 */
class NoCacheWebPathResolverTest extends WebTestCase
{
    public function testCouldBeGetFromContainer()
    {
        $this->createClient();

        $resolver = self::$kernel->getContainer()->get('liip_imagine.cache.resolver.no_cache_web_path');

        $this->assertInstanceOf('Liip\ImagineBundle\Imagine\Cache\Resolver\NoCacheWebPathResolver', $resolver);
    }
}
