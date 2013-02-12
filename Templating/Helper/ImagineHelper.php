<?php

namespace Liip\ImagineBundle\Templating\Helper;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Templating\Helper\Helper;

class ImagineHelper extends Helper
{
    /**
     * @var CacheManager
     */
    protected $cacheManager;

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
     * Gets the browser path for the image and filter to apply.
     *
     * @param string $path
     * @param string $filter
     * @param boolean $absolute
     *
     * @return string
     */
    public function filter($path, $filter, $absolute = false)
    {
        return $this->cacheManager->getBrowserPath($path, $filter, $absolute);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'liip_imagine';
    }
}
