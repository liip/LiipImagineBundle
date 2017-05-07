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

use Liip\ImagineBundle\Imagine\Cache\Resolver\NoCacheWebPathResolver;
use Liip\ImagineBundle\Tests\Functional\AbstractWebTestCase;

/**
 * @covers \Liip\ImagineBundle\Imagine\Cache\Resolver\NoCacheWebPathResolver
 */
class NoCacheWebPathResolverTest extends AbstractWebTestCase
{
    public function testCouldBeGetFromContainer()
    {
        $this->createClient();

        $this->assertInstanceOf(
            NoCacheWebPathResolver::class,
            self::$kernel->getContainer()->get('liip_imagine.cache.resolver.no_cache_web_path')
        );
    }
}
