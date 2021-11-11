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

use Liip\ImagineBundle\DependencyInjection\Compiler\AbstractCompilerPass;
use Liip\ImagineBundle\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @coversNothing
 */
class AbstractCompilerPassTestCase extends AbstractTest
{
    protected function createDefinition(array $tags = []): Definition
    {
        $definition = new Definition();

        foreach ($tags as $name => $attributes) {
            $definition->addTag($name, $attributes);
        }

        return $definition;
    }

    /**
     * @param Definition[] $definitions
     */
    protected function createContainerBuilder(array $definitions = []): ContainerBuilder
    {
        $container = new ContainerBuilder();

        foreach ($definitions as $name => $object) {
            $container->setDefinition($name, $object);
        }

        return $container;
    }

    /**
     * @param string[]     $methods
     * @param Definition[] $definitions
     *
     * @return ContainerBuilder&MockObject
     */
    protected function createContainerBuilderMock(array $methods = [], array $definitions = [])
    {
        $container = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->setMethods($methods)
            ->getMock();

        foreach ($definitions as $name => $object) {
            $container->setDefinition($name, $object);
        }

        return $container;
    }

    protected function expectContainerLogMethodCalledOnce(MockObject $container, ...$expectedArguments): void
    {
        $expectation = $container
            ->expects($this->once())
            ->method('log');

        if (!empty($expectedArguments)) {
            $expectation->with(...$expectedArguments);
        } else {
            $expectation->withAnyParameters();
        }
    }

    protected function assertDefinitionMethodCallsNone(Definition $definition, string $message = ''): void
    {
        $this->assertDefinitionMethodCallCount(0, $definition, $message);
    }

    protected function assertDefinitionMethodCallCount(int $expect, Definition $definition, string $message = ''): void
    {
        $this->assertCount($expect, $definition->getMethodCalls(), $message);
    }

    /**
     * @param Definition[]|array[] $definitions
     */
    protected function assertContainerLogMethodCalledForCompilerPass(AbstractCompilerPass $pass, array $definitions): void
    {
        $container = $this->createContainerBuilderMock(['log'], $definitions[0]);

        $this->expectContainerLogMethodCalledOnce($container);
        $pass->process($container);
    }

    /**
     * @return Definition[]|array[]
     */
    protected function getCompilerPassContainerDefinitions(string $definition, string $manager, array $tags): array
    {
        $m = $this->createDefinition();
        $l = $this->createDefinition($tags);

        return [[
            $definition => $l,
            $manager => $m,
        ], $m];
    }
}
