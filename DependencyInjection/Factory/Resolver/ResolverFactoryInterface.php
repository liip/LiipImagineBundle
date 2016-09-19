<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\DependencyInjection\Factory\Resolver;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

interface ResolverFactoryInterface
{
    /**
     * @param ContainerBuilder $container
     * @param string           $resolverName
     * @param array            $config
     *
     * @return string The resolver service id
     */
    public function create(ContainerBuilder $container, $resolverName, array $config);

    /**
     * The resolver factory name,
     * For example web_path, aws_s3 o amazon_s3.
     *
     * @return string
     */
    public function getName();

    /**
     * @param ArrayNodeDefinition $builder
     */
    public function addConfiguration(ArrayNodeDefinition $builder);
}
