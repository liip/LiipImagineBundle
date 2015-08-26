<?php

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
     * {@inheritDoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('imagine_filter', array($this, 'filter'))
        );
    }

    /**
     * Gets the browser path for the image and filter to apply.
     *
     * @param string $path
     * @param string $filter
     * @param array  $runtimeConfig
     *
     * @return \Twig_Markup
     */
    public function filter($path, $filter, array $runtimeConfig = array())
    {
        return $this->cacheManager->getBrowserPath($path, $filter, $runtimeConfig);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'liip_imagine';
    }
}
