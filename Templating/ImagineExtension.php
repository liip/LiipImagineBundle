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
     * Gets the browser path for the image and filter to apply.
     *
     * @param string $path
     * @param string $filter
     * @param array  $runtimeConfig
     * @param string $resolver
     *
     * @return \Twig_Markup
     */
    public function filter($path, $filter, array $runtimeConfig = array(), $resolver = null)
    {
        return $this->cacheManager->getBrowserPath($path, $filter, $runtimeConfig, $resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'liip_imagine';
    }
}
