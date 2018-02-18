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
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Compiler\LoadersCompilerPass
 */
class LoadersCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testProcess()
    {
        [$d, $m] = $this->getLoadersCompilerPassContainerDefinitions();

        $container = $this->createContainerBuilder($d);

        $pass = new LoadersCompilerPass();

        $this->assertDefinitionMethodCallsNone($m);
        $pass->process($container);
        $this->assertDefinitionMethodCallCount(1, $m);
    }

    public function testProcessLogging()
    {
        $this->assertContainerLogMethodCalledForCompilerPass(
            new LoadersCompilerPass(),
            $this->getLoadersCompilerPassContainerDefinitions()
        );
    }

    /**
     * @return Definition[]|array[]
     */
    private function getLoadersCompilerPassContainerDefinitions(): array
    {
        return $this->getCompilerPassContainerDefinitions(
            'binary.loader.foobar',
            'liip_imagine.data.manager',
            ['liip_imagine.binary.loader' => ['loader' => 'foobar']]
        );
    }
}
