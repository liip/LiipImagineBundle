<?php

namespace Liip\ImagineBundle\Tests\DependencyInjection\Compiler;

use Liip\ImagineBundle\DependencyInjection\Compiler\LoadersCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers Liip\ImagineBundle\DependencyInjection\Compiler\LoadersCompilerPass
 */
class LoadersCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $managerDefinition = new Definition();
        $loaderDefinition = new Definition();
        $loaderDefinition->addTag('liip_imagine.binary.loader', array(
            'loader' => 'foo',
        ));

        $container = new ContainerBuilder();
        $container->setDefinition('liip_imagine.data.manager', $managerDefinition);
        $container->setDefinition('a.binary.loader', $loaderDefinition);

        $pass = new LoadersCompilerPass();

        //guard
        $this->assertCount(0, $managerDefinition->getMethodCalls());

        $pass->process($container);

        $this->assertCount(1, $managerDefinition->getMethodCalls());
    }
}
