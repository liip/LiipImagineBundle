<?php

namespace Liip\ImagineBundle\Tests\Exception\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotStorableException;

/**
 * @covers Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotStorableException
 */
class NotStorableExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testSubClassOfRuntimeException()
    {
        $e = new NotStorableException();

        $this->assertInstanceOf('\RuntimeException', $e);
    }

    public function testImplementsExceptionInterface()
    {
        $e = new NotStorableException();

        $this->assertInstanceOf('Liip\ImagineBundle\Exception\ExceptionInterface', $e);
    }
}
