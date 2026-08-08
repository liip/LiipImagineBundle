<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Exception\Signer;

use Liip\ImagineBundle\Exception\ExceptionInterface;
use Liip\ImagineBundle\Exception\Signer\InvalidSignedUrlException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @covers \Liip\ImagineBundle\Exception\Signer\InvalidSignedUrlException
 */
class InvalidSignedUrlExceptionTest extends TestCase
{
    public function testExtendsBadRequestHttpExceptionAndExposesContext(): void
    {
        $exception = new InvalidSignedUrlException(
            'uploads/foo.jpg',
            'thumbnail',
            ['thumbnail' => ['size' => [50, 50]]]
        );

        $this->assertInstanceOf(BadRequestHttpException::class, $exception);
        $this->assertInstanceOf(ExceptionInterface::class, $exception);
        $this->assertSame('uploads/foo.jpg', $exception->getPath());
        $this->assertSame('thumbnail', $exception->getFilter());
        $this->assertSame(['thumbnail' => ['size' => [50, 50]]], $exception->getRuntimeConfig());
        $this->assertSame(
            'Signed url does not pass the sign check for path "uploads/foo.jpg" and filter "thumbnail" and runtime config {"thumbnail":{"size":[50,50]}}',
            $exception->getMessage()
        );
    }
}
