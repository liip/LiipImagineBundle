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
use Liip\ImagineBundle\Model\FileBinary;
use Symfony\Component\Mime\MimeTypeGuesserInterface;
use Symfony\Component\Mime\MimeTypesInterface;

class FileSystemLoader implements LoaderInterface
{
    /**
     * @var MimeTypeGuesserInterface
     */
    protected $mimeTypeGuesser;

    /**
     * @var MimeTypesInterface
     */
    protected $extensionGuesser;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    public function __construct(
        MimeTypeGuesserInterface $mimeGuesser,
        MimeTypesInterface $extensionGuesser,
        LocatorInterface $locator
    ) {
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
        $mimeType = $this->mimeTypeGuesser->guessMimeType($path);
        $extension = $this->getExtension($mimeType);

        return new FileBinary($path, $mimeType, $extension);
    }

    private function getExtension(?string $mimeType): ?string
    {
        if (null === $mimeType) {
            return null;
        }

        return $this->extensionGuesser->getExtensions($mimeType)[0] ?? null;
    }
}
