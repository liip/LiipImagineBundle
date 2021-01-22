<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Templating;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

trait FilterTrait
{
    /**
     * @var CacheManager
     */
    private $cache;

    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Gets the browser path for the image and filter to apply.
     *
     * @param string      $path
     * @param string      $filter
     * @param string|null $resolver
     * @param int         $referenceType
     *
     * @return string
     */
    public function filter($path, $filter, array $config = [], $resolver = null, $referenceType = UrlGeneratorInterface::ABSOLUTE_URL)
    {
        return $this->cache->getBrowserPath(parse_url($path, PHP_URL_PATH), $filter, $config, $resolver, $referenceType);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'liip_imagine';
    }
}
