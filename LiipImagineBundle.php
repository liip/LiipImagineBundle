<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle;

use Enqueue\Bundle\DependencyInjection\Compiler\AddTopicMetaPass;
use Liip\ImagineBundle\Async\Topics;
use Liip\ImagineBundle\DependencyInjection\Compiler\DriverCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\FiltersCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\LoadersCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\MetadataReaderCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\NonFunctionalFilterExceptionPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\PostProcessorsCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\ResolversCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\ChainLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\FileSystemLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\FlysystemLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\StreamLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\AwsS3ResolverFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\FlysystemResolverFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\WebPathResolverFactory;
use Liip\ImagineBundle\DependencyInjection\LiipImagineExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LiipImagineBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new NonFunctionalFilterExceptionPass());
        $container->addCompilerPass(new DriverCompilerPass());
        $container->addCompilerPass(new LoadersCompilerPass());
        $container->addCompilerPass(new FiltersCompilerPass());
        $container->addCompilerPass(new PostProcessorsCompilerPass());
        $container->addCompilerPass(new ResolversCompilerPass());
        $container->addCompilerPass(new MetadataReaderCompilerPass());

        if (class_exists(AddTopicMetaPass::class)) {
            $container->addCompilerPass(AddTopicMetaPass::create()
                ->add(Topics::CACHE_RESOLVED, 'The topic contains messages about resolved image\'s caches')
            );
        }

        /** @var $extension LiipImagineExtension */
        $extension = $container->getExtension('liip_imagine');

        $extension->addResolverFactory(new WebPathResolverFactory());
        $extension->addResolverFactory(new AwsS3ResolverFactory());
        $extension->addResolverFactory(new FlysystemResolverFactory());

        $extension->addLoaderFactory(new StreamLoaderFactory());
        $extension->addLoaderFactory(new FileSystemLoaderFactory());
        $extension->addLoaderFactory(new FlysystemLoaderFactory());
        $extension->addLoaderFactory(new ChainLoaderFactory());
    }
}
