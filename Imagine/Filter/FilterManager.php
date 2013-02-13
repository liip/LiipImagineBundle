<?php

namespace Liip\ImagineBundle\Imagine\Filter;

use Imagine\Image\ImageInterface;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FilterManager
{
    /**
     * @var FilterConfiguration
     */
    protected $filterConfig;

    /**
     * @var LoaderInterface[]
     */
    protected $loaders = array();

    /**
     * Constructor.
     *
     * @param FilterConfiguration $filterConfig
     */
    public function __construct(FilterConfiguration $filterConfig)
    {
        $this->filterConfig = $filterConfig;
    }

    /**
     * Adds a loader to handle the given filter.
     *
     * @param string $filter
     * @param LoaderInterface $loader
     *
     * @return void
     */
    public function addLoader($filter, LoaderInterface $loader)
    {
        $this->loaders[$filter] = $loader;
    }

    /**
     * @return FilterConfiguration
     */
    public function getFilterConfiguration()
    {
        return $this->filterConfig;
    }

    /**
     * Returns a response containing the given image after applying the given filter on it.
     *
     * @uses FilterManager::applyFilterSet
     *
     * @param Request $request
     * @param string $filter
     * @param ImageInterface $image
     * @param string $localPath
     *
     * @return Response
     */
    public function get(Request $request, $filter, ImageInterface $image, $localPath)
    {
        $config = $this->getFilterConfiguration()->get($filter);

        $image = $this->applyFilter($image, $filter);

        if (empty($config['format'])) {
            $format = pathinfo($localPath, PATHINFO_EXTENSION);
            $format = $format ?: 'png';
        } else {
            $format = $config['format'];
        }

        $quality = empty($config['quality']) ? 100 : $config['quality'];

        $image = $image->get($format, array('quality' => $quality));

        $contentType = $request->getMimeType($format);
        if (empty($contentType)) {
            $contentType = 'image/'.$format;
        }

        return new Response($image, 200, array('Content-Type' => $contentType));
    }

    /**
     * Apply the provided filter set on the given Image.
     *
     * @param ImageInterface $image
     * @param string $filter
     *
     * @return ImageInterface
     *
     * @throws \InvalidArgumentException
     */
    public function applyFilter(ImageInterface $image, $filter)
    {
        $config = $this->getFilterConfiguration()->get($filter);

        foreach ($config['filters'] as $eachFilter => $eachOptions) {
            if (!isset($this->loaders[$eachFilter])) {
                throw new \InvalidArgumentException(sprintf(
                    'Could not find filter loader for "%s" filter type', $eachFilter
                ));
            }

            $image = $this->loaders[$eachFilter]->load($image, $eachOptions);
        }

        return $image;
    }
}
