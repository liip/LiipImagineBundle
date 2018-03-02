<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Imagine\Cache;

use Liip\ImagineBundle\Imagine\Cache\Signer;
use Liip\ImagineBundle\Imagine\Cache\SignerInterface;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Cache\Signer
 */
class SignerTest extends AbstractTest
{
    public function testImplementsSignerInterface()
    {
        $rc = new \ReflectionClass(Signer::class);

        $this->assertTrue($rc->implementsInterface(SignerInterface::class));
    }

    public function testCouldBeConstructedWithSecret()
    {
        $signer = new Signer('aSecret');

        $this->assertInstanceOf(Signer::class, $signer);
    }

    public function testShouldReturnShortHashOnSign()
    {
        $singer = new Signer('aSecret');

        $this->assertSame(8, mb_strlen($singer->sign('aPath')));
    }

    public function testShouldSingAndSuccessfullyCheckPathWithoutRuntimeConfig()
    {
        $singer = new Signer('aSecret');

        $this->assertTrue($singer->check($singer->sign('aPath'), 'aPath'));
    }

    public function testShouldSingAndSuccessfullyCheckPathWithRuntimeConfig()
    {
        $singer = new Signer('aSecret');

        $this->assertTrue($singer->check($singer->sign('aPath', ['aConfig']), 'aPath', ['aConfig']));
    }

    public function testShouldConvertRecursivelyToStringAllRuntimeConfigParameters()
    {
        $singer = new Signer('aSecret');

        $runtimeConfigInts = [
            'foo' => 14,
            'bar' => [
                'bar' => 15,
            ],
        ];

        $runtimeConfigStrings = [
            'foo' => '14',
            'bar' => [
                'bar' => '15',
            ],
        ];

        $this->assertTrue($singer->check($singer->sign('aPath', $runtimeConfigInts), 'aPath', $runtimeConfigStrings));
    }
}
