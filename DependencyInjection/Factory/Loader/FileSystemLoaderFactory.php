<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\DependencyInjection\Factory\Loader;

use Liip\ImagineBundle\Utility\Framework\SymfonyFramework;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

class FileSystemLoaderFactory extends AbstractLoaderFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $loaderName, array $config)
    {
        $dataRoots = $config['data_root'];
        
        // Load bundle resources if requested
        if ($config['bundle_resources']) {
            foreach ($container->getParameter('kernel.bundles') as $bundle) {
                $refClass = new \ReflectionClass($bundle);
                $bundlePath = dirname($refClass->getFileName());
                if (!is_dir($originDir = $bundlePath . '/Resources/public')) {
                    continue;
                }
                $dataRoots[] = realpath($originDir);
            }
        }
        
        $definition = $this->getChildLoaderDefinition();
        $definition->replaceArgument(2, $dataRoots);
        $definition->replaceArgument(3, $this->createLocatorReference($config['locator']));

        return $this->setTaggedLoaderDefinition($loaderName, $definition, $container);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filesystem';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->booleanNode('bundle_resources')
                  ->defaultFalse()
                ->end()
                ->enumNode('locator')
                    ->values(array('filesystem', 'filesystem_insecure'))
                    ->info('Using the "filesystem_insecure" locator is not recommended due to a less secure resolver mechanism, but is provided for those using heavily symlinked projects.')
                    ->defaultValue('filesystem')
                ->end()
                ->arrayNode('data_root')
                    ->beforeNormalization()
                    ->ifString()
                        ->then(function ($value) { return array($value); })
                    ->end()
                    ->defaultValue(array('%kernel.root_dir%/../web'))
                    ->prototype('scalar')
                        ->cannotBeEmpty()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param string $reference
     *
     * @return Reference
     */
    private function createLocatorReference($reference)
    {
        $name = sprintf('liip_imagine.binary.locator.%s', $reference);

        if (SymfonyFramework::hasDefinitionSharing()) {
            return new Reference($name);
        }

        return new Reference($name, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, false);
    }
}
