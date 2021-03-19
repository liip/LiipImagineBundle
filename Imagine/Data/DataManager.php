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
use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Liip\ImagineBundle\Exception\LogicException;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface as DeprecatedExtensionGuesserInterface;
use Symfony\Component\Mime\MimeTypesInterface;

class DataManager
{
    /**
     * @var MimeTypeGuesserInterface
     */
    protected $mimeTypeGuesser;

    /**
     * @var DeprecatedExtensionGuesserInterface|MimeTypesInterface
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
     * @var string|null
     */
    protected $globalDefaultImage;

    /**
     * @var LoaderInterface[]
     */
    protected $loaders = [];

    /**
     * @param DeprecatedExtensionGuesserInterface|MimeTypesInterface $extensionGuesser
     * @param string                                                 $defaultLoader
     * @param string                                                 $globalDefaultImage
     */
    public function __construct(
        MimeTypeGuesserInterface $mimeTypeGuesser,
        $extensionGuesser,
        FilterConfiguration $filterConfig,
        $defaultLoader = null,
        $globalDefaultImage = null
    ) {
        if (!$extensionGuesser instanceof MimeTypesInterface && !$extensionGuesser instanceof DeprecatedExtensionGuesserInterface) {
            throw new InvalidArgumentException('$extensionGuesser must be an instance of Symfony\Component\Mime\MimeTypesInterface or Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface');
        }

        if (interface_exists(MimeTypesInterface::class) && $extensionGuesser instanceof DeprecatedExtensionGuesserInterface) {
            @trigger_error(sprintf('Passing a %s to "%s()" is deprecated since Symfony 4.3, pass a "%s" instead.', DeprecatedExtensionGuesserInterface::class, __METHOD__, MimeTypesInterface::class), E_USER_DEPRECATED);
        }

        $this->mimeTypeGuesser = $mimeTypeGuesser;
        $this->filterConfig = $filterConfig;
        $this->defaultLoader = $defaultLoader;
        $this->extensionGuesser = $extensionGuesser;
        $this->globalDefaultImage = $globalDefaultImage;
    }

    /**
     * Adds a loader to retrieve images for the given filter.
     *
     * @param string $filter
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
     * @throws \InvalidArgumentException
     *
     * @return LoaderInterface
     */
    public function getLoader($filter)
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
     * @param string $filter
     * @param string $path
     *
     * @throws LogicException
     *
     * @return BinaryInterface
     */
    public function find($filter, $path)
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
     *
     * @param string $filter
     *
     * @return string|null
     */
    public function getDefaultImageUrl($filter)
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
        if ($this->extensionGuesser instanceof DeprecatedExtensionGuesserInterface) {
            return $this->extensionGuesser->guess($mimeType);
        }

        if (null === $mimeType) {
            return null;
        }

        return $this->extensionGuesser->getExtensions($mimeType)[0] ?? null;
    }
}
