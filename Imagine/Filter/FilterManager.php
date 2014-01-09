<?php

namespace Liip\ImagineBundle\Imagine\Filter;

use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FilterManager
{
    /**
     * @var FilterConfiguration
     */
    protected $filterConfig;

    /**
     * @var ImagineInterface
     */
    protected $imagine;

    /**
     * @var LoaderInterface[]
     */
    protected $loaders = array();

    /**
     * @param FilterConfiguration $filterConfig
     * @param ImagineInterface    $imagine
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
     * Apply the provided filter set on the given binary.
     *
     * @param BinaryInterface $binary
     * @param string          $filter
     *
     * @throws \InvalidArgumentException
     *
     * @return BinaryInterface
     */
    public function applyFilter(BinaryInterface $binary, $filter)
    {
        $image = $this->imagine->load($binary->getContent());

        $config = $this->getFilterConfiguration()->get($filter);

        foreach ($config['filters'] as $eachFilter => $eachOptions) {
            if (!isset($this->loaders[$eachFilter])) {
                throw new \InvalidArgumentException(sprintf(
                    'Could not find filter loader for "%s" filter type', $eachFilter
                ));
            }

            $image = $this->loaders[$eachFilter]->load($image, $eachOptions);
        }

        $filteredContent = $image->get($binary->getFormat(), array(
            'quality' => array_key_exists('quality', $config) ? $config['quality'] : 100
        ));

        return new Binary($filteredContent, $binary->getMimeType(), $binary->getFormat());
    }
}
