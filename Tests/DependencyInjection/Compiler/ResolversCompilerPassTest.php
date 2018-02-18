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
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Compiler\ResolversCompilerPass
 */
class ResolversCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testProcess()
    {
        [$d, $m] = $this->getResolversCompilerPassContainerDefinitions();

        $container = $this->createContainerBuilder($d);

        $pass = new ResolversCompilerPass();

        $this->assertDefinitionMethodCallsNone($m);
        $pass->process($container);
        $this->assertDefinitionMethodCallCount(1, $m);
    }

    public function testProcessLogging()
    {
        $this->assertContainerLogMethodCalledForCompilerPass(
            new ResolversCompilerPass(),
            $this->getResolversCompilerPassContainerDefinitions()
        );
    }

    /**
     * @return Definition[]|array[]
     */
    private function getResolversCompilerPassContainerDefinitions(): array
    {
        return $this->getCompilerPassContainerDefinitions(
            'resolver.foobar',
            'liip_imagine.cache.manager',
            ['liip_imagine.cache.resolver' => ['resolver' => 'foobar']]
        );
    }
}
