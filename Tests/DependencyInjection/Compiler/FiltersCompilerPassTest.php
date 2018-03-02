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
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Compiler\FiltersCompilerPass
 */
class FiltersCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testProcess()
    {
        [$d, $m] = $this->getFiltersCompilerPassContainerDefinitions();

        $container = $this->createContainerBuilder($d);

        $pass = new FiltersCompilerPass();

        $this->assertDefinitionMethodCallsNone($m);
        $pass->process($container);
        $this->assertDefinitionMethodCallCount(1, $m);
    }

    public function testProcessLogging()
    {
        $this->assertContainerLogMethodCalledForCompilerPass(
            new FiltersCompilerPass(),
            $this->getFiltersCompilerPassContainerDefinitions()
        );
    }

    /**
     * @return Definition[]|array[]
     */
    private function getFiltersCompilerPassContainerDefinitions(): array
    {
        return $this->getCompilerPassContainerDefinitions(
            'filter.loader.foobar',
            'liip_imagine.filter.manager',
            ['liip_imagine.filter.loader' => ['loader' => 'foobar']]
        );
    }
}
