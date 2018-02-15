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

use Symfony\Component\DependencyInjection\ChildDefinition;

abstract class AbstractResolverFactory implements ResolverFactoryInterface
{
    /**
     * @var string
     */
    protected static $namePrefix = 'liip_imagine.cache.resolver';

    /**
     * @param string|null $name
     *
     * @return ChildDefinition
     */
    final protected function getChildResolverDefinition($name = null)
    {
        return new ChildDefinition(sprintf('%s.prototype.%s', static::$namePrefix, $name ?: $this->getName()));
    }
}
