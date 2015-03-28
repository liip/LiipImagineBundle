<?php

namespace Liip\ImagineBundle;

use Liip\ImagineBundle\DependencyInjection\Compiler\FiltersCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\LoadersCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\PostProcessorsCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\ResolversCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\FileSystemLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\StreamLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\AwsS3ResolverFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\WebPathResolverFactory;
use Liip\ImagineBundle\DependencyInjection\LiipImagineExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LiipImagineBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new LoadersCompilerPass());
        $container->addCompilerPass(new FiltersCompilerPass());
        $container->addCompilerPass(new PostProcessorsCompilerPass());
        $container->addCompilerPass(new ResolversCompilerPass());

        /** @var $extension LiipImagineExtension */
        $extension = $container->getExtension('liip_imagine');

        $extension->addResolverFactory(new WebPathResolverFactory());
        $extension->addResolverFactory(new AwsS3ResolverFactory());

        $extension->addLoaderFactory(new StreamLoaderFactory());
        $extension->addLoaderFactory(new FileSystemLoaderFactory());
    }
}
