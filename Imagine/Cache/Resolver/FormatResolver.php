<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RequestContext;

/**
 * Class FormatCacheResolver
 *
 * @copyright 2017 IntechSystems, SIA
 * @package   Liip\ImagineBundle\Imagine\Cache\Resolver
 * @author    Mihail Savluga
 */
class FormatResolver extends WebPathResolver
{
    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @param Filesystem     $filesystem
     * @param RequestContext $requestContext
     * @param string         $webRootDir
     * @param string         $cachePrefix
     * @param FilterManager  $filterManager
     */
    public function __construct(
        Filesystem $filesystem,
        RequestContext $requestContext,
        $webRootDir,
        $cachePrefix = 'media/cache',
        FilterManager $filterManager
    ) {
        parent::__construct($filesystem, $requestContext, $webRootDir, $cachePrefix);

        $this->filterManager = $filterManager;
    }

    /**
     * {@inheritDoc}
     */
    protected function getFilePath($path, $filter)
    {
        return $this->webRoot.'/'.$this->getFileUrl($this->replaceImageFileExtension($path, $filter), $filter);
    }

    /**
     * {@inheritDoc}
     */
    protected function getFileUrl($path, $filter)
    {
        return $this->cachePrefix.'/'.$filter.'/'.ltrim($this->replaceImageFileExtension($path, $filter), '/');
    }

    /**
     * Replaces original image file extension to conversion format extension
     *
     * @param string $path
     * @param string $filter
     *
     * @return mixed
     */
    protected function replaceImageFileExtension($path, $filter)
    {
        $newExtension = $this->getImageFormat($filter);
        if (!is_null($newExtension)) {
            $path = preg_replace('/\.[^.]+$/', '.' . $newExtension, $path);
        }

        return $path;
    }

    /**
     * Returns image conversion format
     *
     * @param $filterName
     *
     * @return mixed
     */
    protected function getImageFormat($filterName)
    {
        $filterConfig = $this->filterManager->getFilterConfiguration();
        $currentFilterConfig = $filterConfig->get($filterName);

        return $currentFilterConfig['format'];
    }
}
