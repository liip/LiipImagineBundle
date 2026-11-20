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

use Liip\ImagineBundle\Imagine\Cache\Resolver\FlysystemV2Resolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\PsrCacheResolver;
use Liip\ImagineBundle\Tests\Functional\AbstractWebTestCase;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Factory\Resolver\FlysystemResolverFactory
 */
class CachedFlysystemResolverTest extends AbstractWebTestCase
{
    private const RESOLVER_SERVICE_ID = 'test.liip_imagine.cache.resolver.cached_flysystem';

    public function testConfiguredCacheWrapsFlysystemResolver(): void
    {
        $this->createClient();

        $resolver = self::$kernel->getContainer()->get(self::RESOLVER_SERVICE_ID);

        $this->assertInstanceOf(PsrCacheResolver::class, $resolver);
        $this->assertInstanceOf(FlysystemV2Resolver::class, $this->getPrivateProperty($resolver, 'resolver'));
    }
}
