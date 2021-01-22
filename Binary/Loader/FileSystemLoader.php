<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Binary\Loader;

use Liip\ImagineBundle\Binary\Locator\LocatorInterface;
use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Liip\ImagineBundle\Model\FileBinary;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface as DeprecatedExtensionGuesserInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface as DeprecatedMimeTypeGuesserInterface;
use Symfony\Component\Mime\MimeTypeGuesserInterface;
use Symfony\Component\Mime\MimeTypesInterface;

class FileSystemLoader implements LoaderInterface
{
    /**
     * @var MimeTypeGuesserInterface|DeprecatedMimeTypeGuesserInterface
     */
    protected $mimeTypeGuesser;

    /**
     * @var MimeTypesInterface|DeprecatedExtensionGuesserInterface
     */
    protected $extensionGuesser;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @param MimeTypeGuesserInterface|DeprecatedMimeTypeGuesserInterface $mimeGuesser
     * @param MimeTypesInterface|DeprecatedExtensionGuesserInterface      $extensionGuesser
     */
    public function __construct(
        $mimeGuesser,
        $extensionGuesser,
        LocatorInterface $locator
    ) {
        if (!$mimeGuesser instanceof MimeTypeGuesserInterface && !$mimeGuesser instanceof DeprecatedMimeTypeGuesserInterface) {
            throw new InvalidArgumentException('$mimeGuesser must be an instance of Symfony\Component\Mime\MimeTypeGuesserInterface or Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface');
        }

        if (!$extensionGuesser instanceof MimeTypesInterface && !$extensionGuesser instanceof DeprecatedExtensionGuesserInterface) {
            throw new InvalidArgumentException('$extensionGuesser must be an instance of Symfony\Component\Mime\MimeTypesInterface or Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface');
        }

        if (interface_exists(MimeTypeGuesserInterface::class) && $mimeGuesser instanceof DeprecatedMimeTypeGuesserInterface) {
            @trigger_error(sprintf('Passing a %s to "%s()" is deprecated since Symfony 4.3, pass a "%s" instead.', DeprecatedMimeTypeGuesserInterface::class, __METHOD__, MimeTypeGuesserInterface::class), E_USER_DEPRECATED);
        }

        if (interface_exists(MimeTypesInterface::class) && $extensionGuesser instanceof DeprecatedExtensionGuesserInterface) {
            @trigger_error(sprintf('Passing a %s to "%s()" is deprecated since Symfony 4.3, pass a "%s" instead.', DeprecatedExtensionGuesserInterface::class, __METHOD__, MimeTypesInterface::class), E_USER_DEPRECATED);
        }

        $this->mimeTypeGuesser = $mimeGuesser;
        $this->extensionGuesser = $extensionGuesser;
        $this->locator = $locator;
    }

    /**
     * {@inheritdoc}
     */
    public function find($path)
    {
        $path = $this->locator->locate($path);
        $mimeType = $this->mimeTypeGuesser instanceof DeprecatedMimeTypeGuesserInterface ? $this->mimeTypeGuesser->guess($path) : $this->mimeTypeGuesser->guessMimeType($path);
        $extension = $this->getExtension($mimeType);

        return new FileBinary($path, $mimeType, $extension);
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
