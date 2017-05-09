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

use Liip\ImagineBundle\DependencyInjection\Factory\ChildDefinitionTrait;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

abstract class AbstractResolverFactory implements ResolverFactoryInterface
{
    use ChildDefinitionTrait;

    /**
     * @var string
     */
    protected static $namePrefix = 'liip_imagine.cache.resolver';

    /**
     * @param string|null $name
     *
     * @return ChildDefinition|DefinitionDecorator
     */
    final protected function getChildResolverDefinition($name = null)
    {
        return $this->getChildDefinition(sprintf('%s.prototype.%s', static::$namePrefix, $name ?: $this->getName()));
    }
}
