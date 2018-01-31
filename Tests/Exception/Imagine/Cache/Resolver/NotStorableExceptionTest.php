<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Exception\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Exception\ExceptionInterface;
use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotStorableException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotStorableException
 */
class NotStorableExceptionTest extends TestCase
{
    public function testSubClassOfRuntimeException()
    {
        $e = new NotStorableException();

        $this->assertInstanceOf(\RuntimeException::class, $e);
    }

    public function testImplementsExceptionInterface()
    {
        $e = new NotStorableException();

        $this->assertInstanceOf(ExceptionInterface::class, $e);
    }
}
