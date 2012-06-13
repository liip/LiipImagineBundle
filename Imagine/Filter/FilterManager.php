<?php

namespace Liip\ImagineBundle\Imagine\Filter;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

class FilterManager
{
    /**
     * @var FilterConfiguration
     */
    private $filterConfig;

    /**
     * @var array
     */
    private $loaders = array();

    /**
     * @param FilterConfiguration $filterConfig
     */
    public function __construct(FilterConfiguration $filterConfig)
    {
        $this->filterConfig = $filterConfig;
    }

    /**
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
     * @param Request $request
     * @param string $filter
     * @param Imagine\Image\ImageInterface $image
     * @param string $localPath
     *
     * @return Response
     */
    public function get(Request $request, $filter, $image, $localPath)
    {
        $config = $this->filterConfig->get($filter);

        foreach ($config['filters'] as $filter => $options) {
            if (!isset($this->loaders[$filter])) {
                throw new \InvalidArgumentException(sprintf(
                    'Could not find filter loader for "%s" filter type', $filter
                ));
            }
            $image = $this->loaders[$filter]->load($image, $options);
        }

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
}
