<?php

namespace Liip\ImagineBundle\Imagine\Data;

use Liip\ImagineBundle\Imagine\Data\Loader\LoaderInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Imagine\MimeTypeGuesserInterface;
use Liip\ImagineBundle\Imagine\RawImage;

class DataManager
{
    /**
     * @var MimeTypeGuesserInterface
     */
    protected $mimeTypeGuesser;

    /**
     * @var FilterConfiguration
     */
    protected $filterConfig;

    /**
     * @var string|null
     */
    protected $defaultLoader;

    /**
     * @var LoaderInterface[]
     */
    protected $loaders = array();

    /**
     * @param MimeTypeGuesserInterface $mimeTypeGuesser
     * @param FilterConfiguration $filterConfig
     * @param string $defaultLoader
     */
    public function __construct(MimeTypeGuesserInterface $mimeTypeGuesser, FilterConfiguration $filterConfig, $defaultLoader = null)
    {
        $this->mimeTypeGuesser = $mimeTypeGuesser;
        $this->filterConfig = $filterConfig;
        $this->defaultLoader = $defaultLoader;
    }

    /**
     * Adds a loader to retrieve images for the given filter.
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
     * Returns a loader previously attached to the given filter.
     *
     * @param string $filter
     *
     * @return LoaderInterface
     *
     * @throws \InvalidArgumentException
     */
    public function getLoader($filter)
    {
        $config = $this->filterConfig->get($filter);

        $loaderName = empty($config['data_loader'])
            ? $this->defaultLoader : $config['data_loader'];

        if (!isset($this->loaders[$loaderName])) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find data loader for "%s" filter type', $filter
            ));
        }

        return $this->loaders[$loaderName];
    }

    /**
     * Retrieves an image with the given filter applied.
     *
     * @param string $filter
     * @param string $path
     *
     * @throws \LogicException
     *
     * @return RawImage
     */
    public function find($filter, $path)
    {
        $loader = $this->getLoader($filter);

        $rawImage = $loader->find($path);
        if (false == $rawImage instanceof RawImage) {
            $rawImage = new RawImage($rawImage, $this->mimeTypeGuesser->guess($rawImage));
        }

        if (null == $rawImage->getMimeType()) {
            throw new \LogicException(sprintf('The mime type of image %s was not guessed.', $path));
        }
        if (0 !== strpos($rawImage->getMimeType(), 'image/')) {
            throw new \LogicException(sprintf('The mime type of image %s must be image/xxx got %s.', $path, $rawImage->getMimeType()));
        }

        return $rawImage;
    }
}
