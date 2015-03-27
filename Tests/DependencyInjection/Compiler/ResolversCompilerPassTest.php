<?php

namespace Liip\ImagineBundle\Tests\DependencyInjection\Compiler;

use Liip\ImagineBundle\DependencyInjection\Compiler\ResolversCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers Liip\ImagineBundle\DependencyInjection\Compiler\ResolversCompilerPass
 */
class ResolversCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $managerDefinition = new Definition();
        $resolverDefinition = new Definition();
        $resolverDefinition->addTag('liip_imagine.cache.resolver', array(
            'resolver' => 'foo',
        ));

        $container = new ContainerBuilder();
        $container->setDefinition('liip_imagine.cache.manager', $managerDefinition);
        $container->setDefinition('a.resolver', $resolverDefinition);

        $pass = new ResolversCompilerPass();

        //guard
        $this->assertCount(0, $managerDefinition->getMethodCalls());

        $pass->process($container);

        $this->assertCount(1, $managerDefinition->getMethodCalls());
    }
}
