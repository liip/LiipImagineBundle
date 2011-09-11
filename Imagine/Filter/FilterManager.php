<?php

namespace Avalanche\Bundle\ImagineBundle\Imagine\Filter;

use Avalanche\Bundle\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

use Imagine\Exception\InvalidArgumentException;
use Imagine\Filter\FilterInterface;
use Imagine\Image\ImagineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FilterManager
{
    /**
     * @var Imagine\Image\ImagineInterface
     */
    private $imagine;

    private $filters;
    private $loaders;
    private $services;

    public function __construct(ImagineInterface $imagine, array $filters = array())
    {
        $this->imagine   = $imagine;
        $this->filters   = $filters;
        $this->loaders   = array();
        $this->services  = array();
    }

    public function addLoader($name, LoaderInterface $loader)
    {
        $this->loaders[$name] = $loader;
    }

    public function get($filter, $image, $realPath = null, $format = 'png')
    {
        if (!isset($this->filters[$filter])) {
            throw new InvalidArgumentException(sprintf(
                'Could not find image filter "%s"', $filter
            ));
        }

        if (is_resource($image)) {
            $image = $this->imagine->load(stream_get_contents($image));
        } else {
            $image = $this->imagine->open($image);
        }

        $options = $this->filters[$filter];

        if (isset($options['type'])) {
            if (!isset($this->loaders[$options['type']])) {
                throw new InvalidArgumentException(sprintf(
                    'Could not find loader for "%s" filter type', $options['type']
                ));
            }

            if (!isset($options['options'])) {
                throw new InvalidArgumentException(sprintf(
                    'Options for filter type "%s" must be specified', $filter
                ));
            }

            $image = $this->loaders[$options['type']]->load($image, $options['options']);
        }

        $quality = empty($options['quality']) ? 100 : $options['quality'];
        if (empty($realPath)) {
            return $image->get($format, array('quality' => $quality));
        }

        $image->save($realPath, array('quality' => $quality));
        return file_get_contents($realPath);
    }
}
