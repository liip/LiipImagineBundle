<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Config\Controller;

use Liip\ImagineBundle\Config\Controller\ControllerConfig;
use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Config\Controller\ControllerConfig
 */
class ControllerConfigTest extends AbstractTest
{
    public static function provideRedirectResponseCodeData(): \Generator
    {
        foreach (ControllerConfig::REDIRECT_RESPONSE_CODES as $code) {
            yield [$code];
        }
    }

    /**
     * @dataProvider provideRedirectResponseCodeData
     */
    public function testRedirectResponseCode(int $redirectResponseCode): void
    {
        $this->assertSame($redirectResponseCode, (new ControllerConfig($redirectResponseCode))->getRedirectResponseCode());
    }

    public static function provideInvalidRedirectResponseCodeData(): \Generator
    {
        foreach ([200, 202, 304, 405, 406, 309, 310] as $code) {
            yield [$code];
        }
    }

    /**
     * @dataProvider provideInvalidRedirectResponseCodeData
     */
    public function testInvalidRedirectResponseCode(int $redirectResponseCode): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Invalid redirect response code "%s" (must be 201, 301, 302, 303, 307, or 308).', $redirectResponseCode
        ));
        $this->assertSame($redirectResponseCode, (new ControllerConfig($redirectResponseCode))->getRedirectResponseCode());
    }
}
