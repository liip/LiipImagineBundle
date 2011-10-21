<?php

namespace Liip\ImagineBundle\Imagine\Filter;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

class FilterManager
{
    /**
     * @var array
     */
    private $filters;

    /**
     * @var array
     */
    private $loaders;

    /**
     * @param array $filters
     */
    public function __construct(array $filters = array())
    {
        $this->filters = $filters;
        $this->loaders = array();
    }

    /**
     * @param $name
     * @param Loader\LoaderInterface $loader
     * 
     * @return void
     */
    public function addLoader($name, LoaderInterface $loader)
    {
        $this->loaders[$name] = $loader;
    }

    /**
     * @param $filter
     *
     * @return array
     */
    public function getFilterConfig($filter)
    {
        if (empty($this->filters[$filter])) {
            new \RuntimeException('Filter not defined: '.$filter);
        }

        return $this->filters[$filter];
    }

    /**
     * @param Request $request
     * @param $filter
     * @param $image
     * @param string $localPath
     *
     * @return Response
     */
    public function get(Request $request, $filter, $image, $localPath)
    {
        if (!isset($this->filters[$filter])) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find image filter "%s"', $filter
            ));
        }

        $config = $this->filters[$filter];

        foreach ($config['filters'] as $filter => $options) {
            if (!isset($this->loaders[$filter])) {
                throw new \InvalidArgumentException(sprintf(
                    'Could not find loader for "%s" filter type', $filter
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
