<?php

namespace Avalanche\Bundle\ImagineBundle\Templating\Helper;

use Avalanche\Bundle\ImagineBundle\Imagine\CachePathResolver;
use Symfony\Component\Templating\Helper\Helper;

class ImagineHelper extends Helper
{
    /**
     * @var Avalanche\Bundle\ImagineBundle\Imagine\CachePathResolver
     */
    private $cachePathResolver;

    /**
     * Constructs by setting $cachePathResolver
     *
     * @param Avalanche\Bundle\ImagineBundle\Imagine\CachePathResolver $cachePathResolver
     */
    public function __construct(CachePathResolver $cachePathResolver)
    {
        $this->cachePathResolver = $cachePathResolver;
    }

    /**
     * Gets cache path of an image to be filtered
     *
     * @param string $path
     * @param string $filter
     *
     * @return string
     */
    public function filter($path, $filter)
    {
        return $this->cachePathResolver->getBrowserPath($path, $filter);
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Templating\Helper.HelperInterface::getName()
     */
    public function getName()
    {
        return 'imagine';
    }
}
