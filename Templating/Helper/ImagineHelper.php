<?php

namespace Liip\ImagineBundle\Templating\Helper;

use Liip\ImagineBundle\Imagine\CachePathResolver;
use Symfony\Component\Templating\Helper\Helper;

class ImagineHelper extends Helper
{
    /**
     * @var Liip\ImagineBundle\Imagine\CachePathResolver
     */
    private $cachePathResolver;

    /**
     * Constructs by setting $cachePathResolver
     *
     * @param Liip\ImagineBundle\Imagine\CachePathResolver $cachePathResolver
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
     * @param boolean $absolute
     *
     * @return string
     */
    public function filter($path, $filter, $absolute = false)
    {
        return $this->cachePathResolver->getBrowserPath($path, $filter, $absolute);
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
