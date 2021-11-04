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

@trigger_error('The '.FilterTrait::class.' trait is deprecated since version 2.7 and will be removed in 3.0; use Twig instead.', E_USER_DEPRECATED);

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @deprecated
 */
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
     * Gets the cache path for the image and filter to apply.
     */
    public function filterCache(
        string $path,
        string $filter,
        array $config = [],
        ?string $resolver = null
    ): string {
        $path = parse_url($path, PHP_URL_PATH);

        if (!empty($config)) {
            $path = $this->cache->getRuntimePath($path, $config);
        }

        return $this->cache->resolve($path, $filter, $resolver);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return 'liip_imagine';
    }
}
