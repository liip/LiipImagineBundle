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

use Liip\ImagineBundle\DependencyInjection\Compiler\FiltersCompilerPass;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Compiler\FiltersCompilerPass
 */
class FiltersCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testProcess()
    {
        $m = $this->createDefinition();
        $l = $this->createDefinition(array('liip_imagine.filter.loader' => array(
            'loader' => 'foobar',
        )));

        $container = $this->createContainerBuilder(array(
            'filter.loader.foobar' => $l,
            'liip_imagine.filter.manager' => $m,
        ));

        $pass = new FiltersCompilerPass();

        $this->assertDefinitionMethodCallsNone($m);
        $pass->process($container);
        $this->assertDefinitionMethodCallCount(1, $m);
    }
}
