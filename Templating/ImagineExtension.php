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
            'imagine_filter' => new \Twig_Filter_Method($this, 'filter'),
        );
    }

    /**
     * Gets the browser path for the image and filter to apply.
     *
     * @param string $path
     * @param string $filter
     * @param array $runtimeConfig
     *
     * @return \Twig_Markup
     */
    public function filter($path, $filter, array $runtimeConfig = array())
    {
        return new \Twig_Markup(
            $this->cacheManager->getBrowserPath($path, $filter, $runtimeConfig),
            'utf8'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'liip_imagine';
    }
}
