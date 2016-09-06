<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

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
