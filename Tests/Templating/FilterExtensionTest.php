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
use Liip\ImagineBundle\Templating\FilterExtension;
use Twig\Extension\AbstractExtension;

/**
 * @covers \Liip\ImagineBundle\Templating\FilterTrait
 * @covers \Liip\ImagineBundle\Templating\FilterExtension
 *
 * @group legacy
 */
class FilterExtensionTest extends AbstractFilterExtensionTest
{
    protected function createExtension(CacheManager $manager): AbstractExtension
    {
        return new FilterExtension($manager);
    }
}
