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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Compiler\AbstractCompilerPass
 * @covers \Liip\ImagineBundle\DependencyInjection\Compiler\LocatorsCompilerPass
 */
class LocatorsCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $locatorDefinition = new Definition();
        $locatorDefinition->addTag('liip_imagine.binary.locator', array(
            'shared' => true,
        ));

        $container = new ContainerBuilder();
        $container->setDefinition('liip_imagine.binary.locator.foo', $locatorDefinition);

        $pass = new LocatorsCompilerPass();

        //guard
        if (method_exists($locatorDefinition, 'isShared')) {
            $this->assertTrue($locatorDefinition->isShared());
        } else {
            $this->assertSame('container', $locatorDefinition->getScope());
        }

        $pass->process($container);

        if (method_exists($locatorDefinition, 'isShared')) {
            $this->assertFalse($locatorDefinition->isShared());
        } else {
            $this->assertSame('prototype', $locatorDefinition->getScope());
        }
    }
}
