<?php


namespace Liip\ImagineBundle\Tests\DependencyInjection\Compiler;

use Liip\ImagineBundle\DependencyInjection\Compiler\ResolversCompilerPass;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers Liip\ImagineBundle\DependencyInjection\Compiler\ResolversCompilerPass
 */
class ResolversCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $d = new Definition();
        $cb = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $cb->expects($this->atLeastOnce())->method('hasDefinition')->with('liip_imagine.cache.manager')->will($this->returnValue(true));
        $cb->expects($this->atLeastOnce())->method('getDefinition')->with('liip_imagine.cache.manager')->will($this->returnValue($d));

        $cb->expects($this->atLeastOnce())->method('findTaggedServiceIds')->with('liip_imagine.cache.resolver')->will($this->returnValue(array(
            'a' => array(array('resolver'=>'foo'))
        )));

        $pass = new ResolversCompilerPass();

        $pass->process($cb);

        $this->assertCount(1,$d->getMethodCalls());
    }
}
 