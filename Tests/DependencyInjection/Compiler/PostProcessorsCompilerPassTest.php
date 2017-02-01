<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\DependencyInjection\Compiler;

use Liip\ImagineBundle\DependencyInjection\Compiler\PostProcessorsCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Compiler\AbstractCompilerPass
 * @covers \Liip\ImagineBundle\DependencyInjection\Compiler\PostProcessorsCompilerPass
 */
class PostProcessorsCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $managerDefinition = new Definition();
        $resolverDefinition = new Definition();
        $resolverDefinition->addTag('liip_imagine.filter.post_processor', array(
            'post_processor' => 'foo',
        ));

        $container = new ContainerBuilder();
        $container->setDefinition('liip_imagine.filter.manager', $managerDefinition);
        $container->setDefinition('a.post_processor', $resolverDefinition);

        $pass = new PostProcessorsCompilerPass();

        $this->assertCount(0, $managerDefinition->getMethodCalls());
        $pass->process($container);
        $this->assertCount(1, $managerDefinition->getMethodCalls());
    }
}
