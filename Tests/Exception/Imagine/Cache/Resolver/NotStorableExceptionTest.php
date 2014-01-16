<?php

namespace Liip\ImagineBundle\Tests\Exception\Imagine\Cache\Resolver;

class NotStorableExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testSubClassOfRuntimeException()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotStorableException');

        $this->assertTrue($rc->isSubclassOf('RuntimeException'));
    }

    public function testImplementsExceptionInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotStorableException');

        $this->assertTrue($rc->implementsInterface('Liip\ImagineBundle\Exception\ExceptionInterface'));
    }
}
