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
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

class FileSystemLoader implements LoaderInterface
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
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @param MimeTypeGuesserInterface  $mimeGuesser
     * @param ExtensionGuesserInterface $extensionGuesser
     * @param LocatorInterface          $locator
     * @param string[]                  $rootPaths
     */
    public function __construct(
        MimeTypeGuesserInterface $mimeGuesser,
        ExtensionGuesserInterface $extensionGuesser,
        LocatorInterface $locator,
        array $rootPaths = []
    ) {
        $this->mimeTypeGuesser = $mimeGuesser;
        $this->extensionGuesser = $extensionGuesser;
        $this->locator = $locator;
        $this->locator->setOptions(['roots' => $rootPaths]);
    }

    /**
     * {@inheritdoc}
     */
    public function find($path)
    {
        $path = $this->locator->locate($path);
        $mime = $this->mimeTypeGuesser->guess($path);

        return new FileBinary($path, $mime, $this->extensionGuesser->guess($mime));
    }
}
