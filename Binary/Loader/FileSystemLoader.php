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

use Liip\ImagineBundle\Binary\Locator\FileSystemLocator;
use Liip\ImagineBundle\Binary\Locator\LocatorInterface;
use Liip\ImagineBundle\Exception\InvalidArgumentException;
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
     * @param string[]                  $dataRoots
     * @param LocatorInterface          $locator
     */
    public function __construct(
        MimeTypeGuesserInterface $mimeGuesser,
        ExtensionGuesserInterface $extensionGuesser,
        $dataRoots
        /* LocatorInterface $locator */
    ) {
        $this->mimeTypeGuesser = $mimeGuesser;
        $this->extensionGuesser = $extensionGuesser;

        if (count($dataRoots) === 0) {
            throw new InvalidArgumentException('One or more data root paths must be specified.');
        }

        if (func_num_args() >= 4 && false === ($this->locator = func_get_arg(3)) instanceof LocatorInterface) {
            throw new \InvalidArgumentException(sprintf('Method %s() expects a LocatorInterface for the forth argument.', __METHOD__));
        } elseif (func_num_args() < 4) {
            @trigger_error(sprintf('Method %s() will have a forth `LocatorInterface $locator` argument in version 2.0. Not defining it is deprecated since version 1.7.2', __METHOD__), E_USER_DEPRECATED);
            $this->locator = new FileSystemLocator();
        }

        $this->locator->setOptions(array('roots' => (array) $dataRoots));
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
