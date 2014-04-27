<?php

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
