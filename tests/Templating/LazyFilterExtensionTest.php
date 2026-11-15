<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Templating;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Templating\LazyFilterExtension;
use Liip\ImagineBundle\Templating\LazyFilterRuntime;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

/**
 * @covers \Liip\ImagineBundle\Templating\LazyFilterExtension
 */
class LazyFilterExtensionTest extends AbstractFilterExtensionTest
{
    protected function createExtension(CacheManager $manager): AbstractExtension
    {
        return new LazyFilterExtension();
    }

    protected function createTwig(CacheManager $manager, string $template): Environment
    {
        $twig = parent::createTwig($manager, $template);
        $twig->addRuntimeLoader(new FactoryRuntimeLoader([
            LazyFilterRuntime::class => static function () use ($manager) {
                return new LazyFilterRuntime($manager);
            },
        ]));

        return $twig;
    }
}
