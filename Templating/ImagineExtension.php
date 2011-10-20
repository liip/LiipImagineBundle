<?php

namespace Liip\ImagineBundle\Templating;

use Liip\ImagineBundle\Imagine\CachePathResolver;
use Symfony\Component\HttpKernel\Util\Filesystem;

class ImagineExtension extends \Twig_Extension
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
     * (non-PHPdoc)
     * @see Twig_Extension::getFilters()
     */
    public function getFilters()
    {
        return array(
            'imagine_filter' => new \Twig_Filter_Method($this, 'filter'),
        );
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
     * @see Twig_ExtensionInterface::getName()
     */
    public function getName()
    {
        return 'imagine';
    }
}
