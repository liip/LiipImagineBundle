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

use Liip\ImagineBundle\Tests\AbstractTest;
use Liip\ImagineBundle\Utility\Framework\SymfonyFramework;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AbstractCompilerPassTestCase extends AbstractTest
{
    /**
     * @param array $tags
     *
     * @return Definition
     */
    protected function createDefinition(array $tags = array())
    {
        $definition = new Definition();

        foreach ($tags as $name => $attributes) {
            $definition->addTag($name, $attributes);
        }

        return $definition;
    }

    /**
     * @param array $definitions
     *
     * @return ContainerBuilder
     */
    protected function createContainerBuilder(array $definitions = array())
    {
        $container = new ContainerBuilder();

        foreach ($definitions as $name => $object) {
            $container->setDefinition($name, $object);
        }

        return $container;
    }

    /**
     * @param Definition  $definition
     * @param string|null $message
     */
    protected function assertDefinitionSharingEnabled(Definition $definition, $message = null)
    {
        if (SymfonyFramework::hasDefinitionSharing()) {
            $this->assertTrue($definition->isShared(), $message);
        } elseif (SymfonyFramework::hasDefinitionScoping()) {
            $this->assertSame('container', $definition->getScope(), $message);
        } else {
            $this->fail(sprintf('Neither sharing or scoping is available for assertion: %s',
                $message ?: var_export($definition)));
        }
    }

    /**
     * @param Definition  $definition
     * @param string|null $message
     */
    protected function assertDefinitionSharingDisabled(Definition $definition, $message = null)
    {
        if (SymfonyFramework::hasDefinitionSharing()) {
            $this->assertFalse($definition->isShared(), $message);
        } elseif (SymfonyFramework::hasDefinitionScoping()) {
            $this->assertSame('prototype', $definition->getScope(), $message);
        } else {
            $this->fail(sprintf('Neither sharing or scoping is available for assertion: %s',
                $message ?: var_export($definition)));
        }
    }

    /**
     * @param Definition  $definition
     * @param string|null $message
     */
    protected function assertDefinitionMethodCallsNone(Definition $definition, $message = null)
    {
        $this->assertDefinitionMethodCallCount(0, $definition, $message);
    }

    /**
     * @param int         $expect
     * @param Definition  $definition
     * @param string|null $message
     */
    protected function assertDefinitionMethodCallCount($expect, Definition $definition, $message = null)
    {
        $this->assertCount($expect, $definition->getMethodCalls(), $message);
    }
}
