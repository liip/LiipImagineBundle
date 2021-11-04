<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Functional\app;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        $bundles = [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Liip\ImagineBundle\LiipImagineBundle(),
            new \Liip\ImagineBundle\Tests\Functional\Fixtures\FooBundle\LiipFooBundle(),
            new \Liip\ImagineBundle\Tests\Functional\Fixtures\BarBundle\LiipBarBundle(),
        ];

        return $bundles;
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/liip_imagine_test/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/liip_imagine_test/cache/logs';
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    /**
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        if (version_compare(self::VERSION, '5.3', '>=')) {
            $loader->load(__DIR__.'/config/symfony_5-3.yaml');
        } else {
            $loader->load(__DIR__.'/config/symfony_legacy.yaml');
        }
        $loader->load(__DIR__.'/config/config.yml');
    }
}