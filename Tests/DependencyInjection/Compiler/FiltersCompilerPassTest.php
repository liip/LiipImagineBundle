<?php


namespace Liip\ImagineBundle\Tests\DependencyInjection\Compiler;

use Liip\ImagineBundle\DependencyInjection\Compiler\FiltersCompilerPass;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers Liip\ImagineBundle\DependencyInjection\Compiler\FiltersCompilerPass
 */
class FiltersCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $d = new Definition();
        $cb = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $cb->expects($this->atLeastOnce())->method('hasDefinition')->with('liip_imagine.filter.manager')->will($this->returnValue(true));
        $cb->expects($this->atLeastOnce())->method('getDefinition')->with('liip_imagine.filter.manager')->will($this->returnValue($d));

        $cb->expects($this->atLeastOnce())->method('findTaggedServiceIds')->with('liip_imagine.filter.loader')->will($this->returnValue(array(
            'a' => array(array('loader'=>'foo'))
        )));

        $pass = new FiltersCompilerPass();

        $pass->process($cb);

        $this->assertCount(1,$d->getMethodCalls());
    }
}
 