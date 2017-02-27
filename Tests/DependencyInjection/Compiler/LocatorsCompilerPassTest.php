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

use Liip\ImagineBundle\DependencyInjection\Compiler\LocatorsCompilerPass;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Compiler\LocatorsCompilerPass
 */
class LocatorsCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testProcess()
    {
        $l = $this->createDefinition(array('liip_imagine.binary.locator' => array(
            'shared' => false,
        )));

        $container = $this->createContainerBuilder(array(
            'liip_imagine.binary.locator.foo' => $l,
        ));

        $pass = new LocatorsCompilerPass();
        $this->assertDefinitionSharingEnabled($l);

        $pass->process($container);
        $this->assertDefinitionSharingDisabled($l);
    }
}
