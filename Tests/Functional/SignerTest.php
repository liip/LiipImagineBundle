<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Functional;

class SignerTest extends WebTestCase
{
    public function testGetAsService()
    {
        $this->createClient();
        $service = self::$kernel->getContainer()->get('liip_imagine.cache.signer');

        $this->assertInstanceOf('Liip\ImagineBundle\Imagine\Cache\SignerInterface', $service);
    }
}
