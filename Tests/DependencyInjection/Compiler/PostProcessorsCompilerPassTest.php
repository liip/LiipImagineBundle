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
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Compiler\PostProcessorsCompilerPass
 */
class PostProcessorsCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testProcess()
    {
        [$d, $m] = $this->getPostProcessorsCompilerPassContainerDefinitions();

        $container = $this->createContainerBuilder($d);

        $pass = new PostProcessorsCompilerPass();

        $this->assertDefinitionMethodCallsNone($m);
        $pass->process($container);
        $this->assertDefinitionMethodCallCount(1, $m);
    }

    public function testProcessLogging()
    {
        $this->assertContainerLogMethodCalledForCompilerPass(
            new PostProcessorsCompilerPass(),
            $this->getPostProcessorsCompilerPassContainerDefinitions()
        );
    }

    /**
     * @return Definition[]|array[]
     */
    private function getPostProcessorsCompilerPassContainerDefinitions(): array
    {
        return $this->getCompilerPassContainerDefinitions(
            'post_processor.foobar',
            'liip_imagine.filter.manager',
            ['liip_imagine.filter.post_processor' => ['post_processor' => 'foobar']]
        );
    }
}
