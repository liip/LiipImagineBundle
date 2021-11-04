<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Fixtures;

use Liip\ImagineBundle\Imagine\Cache\CacheManagerAwareInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;

abstract class CacheManagerAwareResolver implements ResolverInterface, CacheManagerAwareInterface
{
}
