<?php

namespace Liip\ImagineBundle\Tests\DependencyInjection\Factory\Resolver;

use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\RelativeWebPathResolverFactoryFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\ResolverFactoryInterface;

class RelativeWebPathResolverFactoryTest extends AbstractWebPathResolverTest
{
    public function testReturnExpectedName()
    {
        $resolver = new RelativeWebPathResolverFactoryFactory();
        
        $this->assertEquals('relative_web_path', $resolver->getName());
    }
    
    /**
     * @return string|ResolverFactoryInterface
     */
    protected function getClassName()
    {
        return RelativeWebPathResolverFactoryFactory::class;
    }
}
