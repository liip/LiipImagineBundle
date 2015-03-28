<?php

namespace Liip\ImagineBundle\Tests\DependencyInjection\Compiler;

use Liip\ImagineBundle\DependencyInjection\Compiler\FiltersCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers Liip\ImagineBundle\DependencyInjection\Compiler\FiltersCompilerPass
 */
class FiltersCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $managerDefinition = new Definition();
        $loaderDefinition = new Definition();
        $loaderDefinition->addTag('liip_imagine.filter.loader', array(
            'loader' => 'foo',
        ));

        $container = new ContainerBuilder();
        $container->setDefinition('liip_imagine.filter.manager', $managerDefinition);
        $container->setDefinition('a.loader', $loaderDefinition);

        $pass = new FiltersCompilerPass();

        //guard
        $this->assertCount(0, $managerDefinition->getMethodCalls());

        $pass->process($container);

        $this->assertCount(1, $managerDefinition->getMethodCalls());
    }
}
