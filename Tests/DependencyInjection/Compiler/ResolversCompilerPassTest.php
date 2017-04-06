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

use Liip\ImagineBundle\DependencyInjection\Compiler\ResolversCompilerPass;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Compiler\ResolversCompilerPass
 */
class ResolversCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testProcess()
    {
        $m = $this->createDefinition();
        $l = $this->createDefinition(array('liip_imagine.cache.resolver' => array(
            'resolver' => 'foobar',
        )));

        $container = $this->createContainerBuilder(array(
            'resolver.foobar' => $l,
            'liip_imagine.cache.manager' => $m,
        ));

        $pass = new ResolversCompilerPass();

        $this->assertDefinitionMethodCallsNone($m);
        $pass->process($container);
        $this->assertDefinitionMethodCallCount(1, $m);
    }
}
