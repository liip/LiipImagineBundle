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

use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException;

/**
 * @covers Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException
 */
class NotResolvableExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testSubClassOfRuntimeException()
    {
        $e = new NotResolvableException();

        $this->assertInstanceOf('\RuntimeException', $e);
    }

    public function testImplementsExceptionInterface()
    {
        $e = new NotResolvableException();

        $this->assertInstanceOf('Liip\ImagineBundle\Exception\ExceptionInterface', $e);
    }
}
