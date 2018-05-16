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

use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\RelativeWebPathResolverFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\ResolverFactoryInterface;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Factory\Resolver\RelativeWebPathResolverFactory
 */
class RelativeWebPathResolverFactoryTest extends AbstractWebPathResolverTest
{
    public function testReturnExpectedName()
    {
        $resolver = new RelativeWebPathResolverFactory;
        
        $this->assertEquals('relative_web_path', $resolver->getName());
    }
    
    /**
     * @return string|ResolverFactoryInterface
     */
    protected function getClassName()
    {
        return RelativeWebPathResolverFactory::class;
    }
}
