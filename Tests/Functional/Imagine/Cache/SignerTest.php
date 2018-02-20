<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Functional\Imagine\Cache;

use Liip\ImagineBundle\Imagine\Cache\SignerInterface;
use Liip\ImagineBundle\Tests\Functional\AbstractWebTestCase;

/**
 * @covers \Liip\ImagineBundle\Imagine\Cache\Signer
 */
class SignerTest extends AbstractWebTestCase
{
    public function testGetAsService()
    {
        $this->createClient();

        $this->assertInstanceOf(SignerInterface::class, self::$kernel->getContainer()->get('liip_imagine.cache.signer'));
    }
}
