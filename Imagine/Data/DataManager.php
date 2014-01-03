<?php

namespace Liip\ImagineBundle\Imagine\Data;

use Liip\ImagineBundle\Model\Filter\ConfigurationCollection;
use Liip\ImagineBundle\Imagine\MimeTypeGuesserInterface;
use Liip\ImagineBundle\Imagine\RawImage;

class DataManager
{
    /**
     * @var MimeTypeGuesserInterface
     */
    protected $mimeTypeGuesser;

    /**
     * @var ConfigurationCollection
     */
    protected $configurations;

    /**
     * Constructor.
     *
     * @param MimeTypeGuesserInterface $mimeTypeGuesser
     * @param ConfigurationCollection  $configurations
     */
    public function __construct(MimeTypeGuesserInterface $mimeTypeGuesser, ConfigurationCollection $configurations)
    {
        $this->mimeTypeGuesser = $mimeTypeGuesser;
        $this->configurations = $configurations;
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
        $loader = $this->configurations->getConfiguration($filter)->getLoader();

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
