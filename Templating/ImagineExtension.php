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

class ImagineExtension extends \Twig_Extension
{
    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * Constructor.
     *
     * @param CacheManager $cacheManager
     */
    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('imagine_filter', array($this, 'filter')),
        );
    }

    /**
     * Gets the browser path for the image and filter to apply. If your path inadvertently contains a query string
     * - which might happen if you use asset versioning - the query string will be stripped from the path, the
     * URL will be resolved using the path without query string, and the stripped query string will be appended to
     * the resulting URL.
     *
     * @param string $path
     * @param string $filter
     * @param array  $runtimeConfig
     * @param string $resolver
     *
     * @return string
     */
    public function filter($path, $filter, array $runtimeConfig = array(), $resolver = null)
    {
        $pathParts = explode('?', $path, 2);
        $url = $this->cacheManager->getBrowserPath($pathParts[0], $filter, $runtimeConfig, $resolver);
        if (empty($pathParts[1])) {
            return $url;
        }

        return $url.(strpos($url, '?') ? '&' : '?').$pathParts[1];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'liip_imagine';
    }
}
