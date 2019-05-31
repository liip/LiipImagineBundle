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
use Symfony\Component\Mime\MimeTypesInterface;

class FileSystemLoader implements LoaderInterface
{
    /**
     * @var MimeTypeGuesserInterface
     */
    protected $mimeTypeGuesser;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @param MimeTypeGuesserInterface $mimeGuesser
     * @param LocatorInterface         $locator
     */
    public function __construct(
        MimeTypesInterface $mimeGuesser,
        LocatorInterface $locator
    ) {
        $this->mimeTypeGuesser = $mimeGuesser;
        $this->locator = $locator;
    }

    /**
     * {@inheritdoc}
     */
    public function find($path)
    {
        $path = $this->locator->locate($path);
        $mime = $this->mimeTypeGuesser->guessMimeType($path);

        return new FileBinary($path, $mime, $this->mimeTypeGuesser->getExtensions($mime)[0] ?? null);
    }
}
