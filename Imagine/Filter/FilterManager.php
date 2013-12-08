<?php

namespace Liip\ImagineBundle\Imagine\Filter;

use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
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
     * @var ImagineInterface
     */
    protected $imagine;

    /**
     * Constructor.
     *
     * @param FilterConfiguration $filterConfig
     */
    public function __construct(FilterConfiguration $filterConfig, ImagineInterface $imagine)
    {
        $this->filterConfig = $filterConfig;
        $this->imagine = $imagine;
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
     * @deprecated
     *
     * Returns a response containing the given filtered image.
     *
     * @param Request $request
     * @param string $filter
     * @param ImageInterface $filteredImage
     * @param string $path
     *
     * @return Response
     */
    public function get(Request $request, $filter, ImageInterface $filteredImage, $path)
    {
        $config = $this->getFilterConfiguration()->get($filter, array(
            'format' => pathinfo($path, PATHINFO_EXTENSION),
        ));

        $filteredImage = $filteredImage->get($config['format'], array('quality' => $config['quality']));

        $contentType = $request->getMimeType($config['format']);
        if (empty($contentType)) {
            $contentType = 'image/'.$config['format'];
        }

        return new Response($filteredImage, 200, array('Content-Type' => $contentType));
    }

    /**
     * Apply the provided filter set on the given Image.
     *
     * @param ImageInterface $image
     * @param string $filter
     * @param array $runtimeConfig
     *
     * @return ImageInterface
     *
     * @throws \InvalidArgumentException
     */
    public function applyFilter(ImageInterface $image, $filter, array $runtimeConfig = array())
    {
        $config = $this->getFilterConfiguration()->get($filter, $runtimeConfig);
        foreach ($config['filters'] as $eachFilter => $eachOptions) {
            if (!isset($this->loaders[$eachFilter])) {
                throw new \InvalidArgumentException(sprintf(
                    'Could not find filter loader for "%s" filter type', $eachFilter
                ));
            }

            $image = $this->loaders[$eachFilter]->load($image, $eachOptions);
        }

        return $this->imagine->load(
            $image->get($config['format'], array('quality' => $config['quality']))
        );
    }
}
