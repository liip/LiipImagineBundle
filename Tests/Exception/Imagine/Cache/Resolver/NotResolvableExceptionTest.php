<?php

namespace Liip\ImagineBundle\Tests\Exception\Imagine\Cache\Resolver;

class NotResolvableExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testSubClassOfRuntimeException()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException');

        $this->assertTrue($rc->isSubclassOf('RuntimeException'));
    }

    public function testImplementsExceptionInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException');

        $this->assertTrue($rc->implementsInterface('Liip\ImagineBundle\Exception\ExceptionInterface'));
    }
}
