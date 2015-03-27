<?php

namespace Liip\ImagineBundle\Tests\Fixtures;

use Liip\ImagineBundle\Imagine\Cache\CacheManagerAwareInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;

abstract class CacheManagerAwareResolver implements ResolverInterface, CacheManagerAwareInterface
{
}
