<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\DependencyInjection\Factory\Resolver;

use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\FormatExtensionResolverFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\ResolverFactoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Factory\Resolver\FormatExtensionResolverFactory<extended>
 */
class FormatExtensionResolverFactoryTest extends TestCase
{
    public function testImplementsResolverFactoryInterface(): void
    {
        $rc = new \ReflectionClass(FormatExtensionResolverFactory::class);

        $this->assertTrue($rc->implementsInterface(ResolverFactoryInterface::class));
    }

    public function testReturnExpectedName(): void
    {
        $resolver = new FormatExtensionResolverFactory();

        $this->assertSame('format_extension', $resolver->getName());
    }

    public function testCreateResolverDefinitionOnCreate(): void
    {
        $container = new ContainerBuilder();

        $resolver = new FormatExtensionResolverFactory();

        $resolverId = $resolver->create($container, 'the_resolver_name', [
            'resolver' => 'the_decorated_resolver',
        ]);

        $this->assertSame('liip_imagine.cache.resolver.the_resolver_name', $resolverId);
        $this->assertTrue($container->hasDefinition($resolverId));

        $resolverDefinition = $container->getDefinition($resolverId);
        $this->assertInstanceOf(ChildDefinition::class, $resolverDefinition);
        $this->assertSame('liip_imagine.cache.resolver.prototype.format_extension', $resolverDefinition->getParent());

        $decorated = $resolverDefinition->getArgument(0);
        $this->assertInstanceOf(Reference::class, $decorated);
        $this->assertSame('liip_imagine.cache.resolver.the_decorated_resolver', (string) $decorated);

        $this->assertSame(
            [['resolver' => 'the_resolver_name']],
            $resolverDefinition->getTag('liip_imagine.cache.resolver')
        );
    }

    public function testProcessCorrectlyOptionsOnAddConfiguration(): void
    {
        $treeBuilder = new TreeBuilder('format_extension');
        $resolver = new FormatExtensionResolverFactory();
        $resolver->addConfiguration($treeBuilder->getRootNode());

        $config = (new Processor())->process($treeBuilder->buildTree(), [
            'format_extension' => ['resolver' => 'default'],
        ]);

        $this->assertSame(['resolver' => 'default'], $config);
    }

    public function testThrowIfResolverNotSetOnAddConfiguration(): void
    {
        $treeBuilder = new TreeBuilder('format_extension');
        $resolver = new FormatExtensionResolverFactory();
        $resolver->addConfiguration($treeBuilder->getRootNode());

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/"resolver"/');

        (new Processor())->process($treeBuilder->buildTree(), [
            'format_extension' => [],
        ]);
    }
}
