<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Service;

use Liip\ImagineBundle\Service\FormatNegotiator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Liip\ImagineBundle\Service\FormatNegotiator
 */
class FormatNegotiatorTest extends TestCase
{
    private $mimeMap = [
        'webp' => ['image/webp'],
        'avif' => ['image/avif'],
    ];

    public function testIsFormatAccepted(): void
    {
        $negotiator = new FormatNegotiator($this->mimeMap);

        $request = new Request([], [], [], [], [], ['HTTP_ACCEPT' => 'image/webp,image/apng,image/*,*/*;q=0.8']);
        $this->assertTrue($negotiator->isFormatAccepted('webp', $request));
        $this->assertFalse($negotiator->isFormatAccepted('avif', $request));

        $request = new Request([], [], [], [], [], ['HTTP_ACCEPT' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8']);
        $this->assertTrue($negotiator->isFormatAccepted('avif', $request));
        $this->assertTrue($negotiator->isFormatAccepted('webp', $request));

        $request = new Request([], [], [], [], [], ['HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8']);
        $this->assertFalse($negotiator->isFormatAccepted('webp', $request));
    }

    public function testNegotiate(): void
    {
        $negotiator = new FormatNegotiator($this->mimeMap);
        $config = [
            'avif' => ['generate' => true, 'priority' => 10],
            'webp' => ['generate' => true, 'priority' => 20],
        ];

        // Case 1: Client prefers AVIF (equal q, but avif is first in Accept or higher priority?)
        // In my implementation, if q is equal, it uses priority from config.
        $request = new Request([], [], [], [], [], ['HTTP_ACCEPT' => 'image/avif,image/webp']);
        $result = $negotiator->negotiate($request, $config);
        $this->assertSame(['webp', 'avif'], $result); // webp has higher priority (20 > 10)

        // Case 2: Client prefers AVIF with higher q
        $request = new Request([], [], [], [], [], ['HTTP_ACCEPT' => 'image/avif;q=1.0,image/webp;q=0.9']);
        $result = $negotiator->negotiate($request, $config);
        $this->assertSame(['avif', 'webp'], $result);

        // Case 3: One format disabled
        $configDisabled = $config;
        $configDisabled['webp']['generate'] = false;
        $request = new Request([], [], [], [], [], ['HTTP_ACCEPT' => 'image/avif,image/webp']);
        $result = $negotiator->negotiate($request, $configDisabled);
        $this->assertSame(['avif'], $result);

        // Case 4: Client supports nothing from config
        $request = new Request([], [], [], [], [], ['HTTP_ACCEPT' => 'image/jpeg']);
        $result = $negotiator->negotiate($request, $config);
        $this->assertSame([], $result);
    }

    public function testGetAcceptedFormats(): void
    {
        $negotiator = new FormatNegotiator($this->mimeMap);

        $request = new Request([], [], [], [], [], ['HTTP_ACCEPT' => 'image/avif;q=1.0,image/webp;q=0.8']);
        $result = $negotiator->getAcceptedFormats($request);

        $this->assertSame(['avif' => 1.0, 'webp' => 0.8], $result);
    }

    public function testRegisterMimeTypes(): void
    {
        $negotiator = new FormatNegotiator();
        $negotiator->registerMimeTypes('png', ['image/png']);

        $request = new Request([], [], [], [], [], ['HTTP_ACCEPT' => 'image/png']);
        $this->assertTrue($negotiator->isFormatAccepted('png', $request));
    }
}
