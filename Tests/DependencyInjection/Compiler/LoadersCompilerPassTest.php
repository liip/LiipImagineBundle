<?php


namespace Liip\ImagineBundle\Tests\DependencyInjection\Compiler;

use Liip\ImagineBundle\DependencyInjection\Compiler\LoadersCompilerPass;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers Liip\ImagineBundle\DependencyInjection\Compiler\LoadersCompilerPass
 */
class LoadersCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $d = new Definition();
        $cb = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $cb->expects($this->atLeastOnce())->method('hasDefinition')->with('liip_imagine.data.manager')->will($this->returnValue(true));
        $cb->expects($this->atLeastOnce())->method('getDefinition')->with('liip_imagine.data.manager')->will($this->returnValue($d));

        $cb->expects($this->atLeastOnce())->method('findTaggedServiceIds')->with('liip_imagine.binary.loader')->will($this->returnValue(array(
            'a' => array(array('loader'=>'foo'))
        )));

        $pass = new LoadersCompilerPass();

        $pass->process($cb);

        $this->assertCount(1,$d->getMethodCalls());
    }
}
 