<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Data;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Binary\MimeTypeGuesserInterface;
use Liip\ImagineBundle\Exception\LogicException;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\Mime\MimeTypesInterface;

class DataManager
{
    protected MimeTypeGuesserInterface $mimeTypeGuesser;

    protected MimeTypesInterface $extensionGuesser;

    protected FilterConfiguration $filterConfig;

    protected ?string $defaultLoader;

    protected ?string $globalDefaultImage;

    /**
     * @var LoaderInterface[]
     */
    protected array $loaders = [];

    public function __construct(
        MimeTypeGuesserInterface $mimeTypeGuesser,
        MimeTypesInterface $extensionGuesser,
        FilterConfiguration $filterConfig,
        string $defaultLoader = null,
        string $globalDefaultImage = null
    ) {
        $this->mimeTypeGuesser = $mimeTypeGuesser;
        $this->filterConfig = $filterConfig;
        $this->defaultLoader = $defaultLoader;
        $this->extensionGuesser = $extensionGuesser;
        $this->globalDefaultImage = $globalDefaultImage;
    }

    /**
     * Adds a loader to retrieve images for the given filter.
     */
    public function addLoader(string $filter, LoaderInterface $loader): void
    {
        $this->loaders[$filter] = $loader;
    }

    /**
     * Returns a loader previously attached to the given filter.
     *
     * @throws \InvalidArgumentException
     */
    public function getLoader(string $filter): LoaderInterface
    {
        $config = $this->filterConfig->get($filter);

        $loaderName = empty($config['data_loader']) ? $this->defaultLoader : $config['data_loader'];

        if (!isset($this->loaders[$loaderName])) {
            throw new \InvalidArgumentException(sprintf('Could not find data loader "%s" for "%s" filter type', $loaderName, $filter));
        }

        return $this->loaders[$loaderName];
    }

    /**
     * Retrieves an image with the given filter applied.
     *
     * @throws LogicException
     */
    public function find(string $filter, string $path): BinaryInterface
    {
        $loader = $this->getLoader($filter);

        $binary = $loader->find($path);
        if (!$binary instanceof BinaryInterface) {
            $mimeType = $this->mimeTypeGuesser->guess($binary);

            $extension = $this->getExtension($mimeType);
            $binary = new Binary(
                $binary,
                $mimeType,
                $extension
            );
        }

        if (null === $binary->getMimeType()) {
            throw new LogicException(sprintf('The mime type of image %s was not guessed.', $path));
        }

        if (0 !== mb_strpos($binary->getMimeType(), 'image/') && 'application/pdf' !== $binary->getMimeType()) {
            throw new LogicException(sprintf('The mime type of file %s must be image/xxx or application/pdf, got %s.', $path, $binary->getMimeType()));
        }

        return $binary;
    }

    /**
     * Get default image url with the given filter applied.
     */
    public function getDefaultImageUrl(string $filter): ?string
    {
        $config = $this->filterConfig->get($filter);

        $defaultImage = null;
        if (false === empty($config['default_image'])) {
            $defaultImage = $config['default_image'];
        } elseif (!empty($this->globalDefaultImage)) {
            $defaultImage = $this->globalDefaultImage;
        }

        return $defaultImage;
    }

    private function getExtension(?string $mimeType): ?string
    {
        if (null === $mimeType) {
            return null;
        }

        return $this->extensionGuesser->getExtensions($mimeType)[0] ?? null;
    }
}
