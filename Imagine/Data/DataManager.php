<?php

namespace Liip\ImagineBundle\Imagine\Data;

use Liip\ImagineBundle\Imagine\Data\Loader\LoaderInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Binary\MimeTypeGuesserInterface;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;

class DataManager
{
    /**
     * @var MimeTypeGuesserInterface
     */
    protected $mimeTypeGuesser;

    /**
     * @var ExtensionGuesserInterface
     */
    protected $extensionGuesser;

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
     * @param MimeTypeGuesserInterface  $mimeTypeGuesser
     * @param ExtensionGuesserInterface $extensionGuesser
     * @param FilterConfiguration       $filterConfig
     * @param string                    $defaultLoader
     */
    public function __construct(
        MimeTypeGuesserInterface $mimeTypeGuesser,
        ExtensionGuesserInterface $extensionGuesser,
        FilterConfiguration $filterConfig,
        $defaultLoader = null
    ) {
        $this->mimeTypeGuesser = $mimeTypeGuesser;
        $this->filterConfig = $filterConfig;
        $this->defaultLoader = $defaultLoader;
        $this->extensionGuesser = $extensionGuesser;
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
     * @return \Liip\ImagineBundle\Binary\BinaryInterface
     */
    public function find($filter, $path)
    {
        $loader = $this->getLoader($filter);

        $binary = $loader->find($path);
        if (!$binary instanceof BinaryInterface) {
            $mimeType = $this->mimeTypeGuesser->guess($binary);

            $binary = new Binary(
                $binary,
                $mimeType,
                $this->extensionGuesser->guess($mimeType)
            );
        }

        if (null === $binary->getMimeType()) {
            throw new \LogicException(sprintf('The mime type of image %s was not guessed.', $path));
        }

        if (0 !== strpos($binary->getMimeType(), 'image/')) {
            throw new \LogicException(sprintf('The mime type of image %s must be image/xxx got %s.', $path, $binary->getMimeType()));
        }

        return $binary;
    }
}
