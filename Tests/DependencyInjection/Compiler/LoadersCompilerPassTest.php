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

use Liip\ImagineBundle\DependencyInjection\Compiler\LoadersCompilerPass;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Compiler\LoadersCompilerPass
 */
class LoadersCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testProcess()
    {
        $m = $this->createDefinition();
        $l = $this->createDefinition(array('liip_imagine.binary.loader' => array(
            'loader' => 'foobar',
        )));

        $container = $this->createContainerBuilder(array(
            'binary.loader.foobar' => $l,
            'liip_imagine.data.manager' => $m,
        ));

        $pass = new LoadersCompilerPass();

        $this->assertDefinitionMethodCallsNone($m);
        $pass->process($container);
        $this->assertDefinitionMethodCallCount(1, $m);
    }
}
