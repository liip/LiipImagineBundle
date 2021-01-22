<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RequestContext;

class FormatResolver extends WebPathResolver
{
    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @param string $webRootDir
     * @param string $cachePrefix
     */
    public function __construct(
        Filesystem $filesystem,
        RequestContext $requestContext,
        $webRootDir,
        $cachePrefix,
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
     */
    protected function replaceImageFileExtension($path, $filter)
    {
        $newExtension = $this->getImageFormat($filter);
        if (null !== $newExtension) {
            $path = preg_replace('/\.[^.]+$/', '.'.$newExtension, $path);
        }

        return $path;
    }

    /**
     * Returns image conversion format
     *
     * @param $filterName
     */
    protected function getImageFormat($filterName)
    {
        $filterConfig = $this->filterManager->getFilterConfiguration();
        $currentFilterConfig = $filterConfig->get($filterName);

        return $currentFilterConfig['format'];
    }
}
