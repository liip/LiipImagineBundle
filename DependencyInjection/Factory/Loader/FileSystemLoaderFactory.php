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

use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class FileSystemLoaderFactory extends AbstractLoaderFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $loaderName, array $config)
    {
        $definition = $this->getChildLoaderDefinition();
        $definition->replaceArgument(2, new Reference($this->setupLocator($loaderName, $config, $container)));

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
                ->enumNode('locator')
                    ->values(array('filesystem', 'filesystem_insecure'))
                    ->info('Using the "filesystem_insecure" locator is not recommended due to a less secure resolver mechanism, but is provided for those using heavily symlinked projects.')
                    ->defaultValue('filesystem')
                ->end()
                ->arrayNode('data_root')
                    ->beforeNormalization()
                    ->ifString()
                        ->then(function ($value) {
                            return array($value);
                        })
                    ->end()
                    ->treatNullLike(array())
                    ->treatFalseLike(array())
                    ->defaultValue(array('%kernel.root_dir%/../web'))
                    ->prototype('scalar')
                        ->cannotBeEmpty()
                    ->end()
                ->end()
                ->arrayNode('bundle_resources')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                        ->end()
                        ->enumNode('access_control_type')
                            ->values(array('blacklist', 'whitelist'))
                            ->info('Sets the access control method applied to bundle names in "access_control_list" into a blacklist or whitelist.')
                            ->defaultValue('blacklist')
                        ->end()
                        ->arrayNode('access_control_list')
                            ->defaultValue(array())
                            ->prototype('scalar')
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /*
     * @param string[]         $staticPaths
     * @param array            $config
     * @param ContainerBuilder $container
     *
     * @return string[]
     */
    private function resolveDataRoots(array $staticPaths, array $config, ContainerBuilder $container)
    {
        if (false === $config['enabled']) {
            return $staticPaths;
        }

        $resourcePaths = array();

        foreach ($this->getBundleResourcePaths($container) as $name => $path) {
            if (('whitelist' === $config['access_control_type']) === in_array($name, $config['access_control_list']) && is_dir($path)) {
                $resourcePaths[$name] = $path;
            }
        }

        return array_merge($staticPaths, $resourcePaths);
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return string[]
     */
    private function getBundleResourcePaths(ContainerBuilder $container)
    {
        if ($container->hasParameter('kernel.bundles_metadata')) {
            $paths = $this->getBundlePathsUsingMetadata($container->getParameter('kernel.bundles_metadata'));
        } else {
            $paths = $this->getBundlePathsUsingNamedObj($container->getParameter('kernel.bundles'));
        }

        return array_map(function ($path) {
            return $path.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'public';
        }, $paths);
    }

    /**
     * @param array[] $metadata
     *
     * @return string[]
     */
    private function getBundlePathsUsingMetadata(array $metadata)
    {
        return array_combine(array_keys($metadata), array_map(function ($data) {
            return $data['path'];
        }, $metadata));
    }

    /**
     * @param string[] $classes
     *
     * @return string[]
     */
    private function getBundlePathsUsingNamedObj(array $classes)
    {
        $paths = array();

        foreach ($classes as $c) {
            try {
                $r = new \ReflectionClass($c);
            } catch (\ReflectionException $exception) {
                throw new InvalidArgumentException(sprintf('Unable to resolve bundle "%s" while auto-registering bundle resource paths.', $c), null, $exception);
            }

            $paths[$r->getShortName()] = dirname($r->getFileName());
        }

        return $paths;
    }

    /**
     * @param string           $loaderName
     * @param array            $config
     * @param ContainerBuilder $container
     *
     * @return string
     */
    private function setupLocator($loaderName, array $config, ContainerBuilder $container)
    {
        $locator = $this->getLocatorDefinition($config);
        $locator->replaceArgument(0, $this->resolveDataRoots($config['data_root'], $config['bundle_resources'], $container));

        $locatorId = sprintf('liip_imagine.binary.locator.%s.%s', $config['locator'], $loaderName);
        $container->setDefinition($locatorId, $locator);

        return $locatorId;
    }

    /**
     * @param array $config
     *
     * @return Definition
     */
    private function getLocatorDefinition(array $config)
    {
        $key = sprintf('liip_imagine.binary.locator.%s', $config['locator']);
        $def = class_exists('\Symfony\Component\DependencyInjection\ChildDefinition') ?
            new ChildDefinition($key) : new DefinitionDecorator($key);

        return $def;
    }
}
