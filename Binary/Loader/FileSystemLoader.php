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
     * @param string[]                  $locatorOrDataRoots
     * @param LocatorInterface          $locator
     */
    public function __construct(
        MimeTypeGuesserInterface $mimeGuesser,
        ExtensionGuesserInterface $extensionGuesser,
        $locatorOrDataRoots
        /* LocatorInterface $locator */
    ) {
        $this->mimeTypeGuesser = $mimeGuesser;
        $this->extensionGuesser = $extensionGuesser;

        if (is_array($locatorOrDataRoots) || is_string($locatorOrDataRoots)) { // pre-1.9.0 behaviour
            if (count((array) $locatorOrDataRoots) === 0) {
                throw new InvalidArgumentException('One or more data root paths must be specified.');
            }

            if (func_num_args() >= 4) {
                if (func_get_arg(3) instanceof LocatorInterface) {
                    @trigger_error(
                        sprintf(
                            'Passing a LocatorInterface as fourth parameter to %s() is deprecated. It needs to be the third parameter. ' .
                            'The previous third parameter (data roots) is removed and the data roots must now be passed as a constructor argument ' .
                            'to the LocatorInterface passed to this method.',
                            __METHOD__
                        ),
                        E_USER_DEPRECATED
                    );

                    $this->locator = func_get_arg(3);
                    $this->locator->setOptions(array('roots' => (array) $locatorOrDataRoots));
                } else {
                    throw new \InvalidArgumentException(sprintf('Unknown call to %s(). Please check the method signature.', __METHOD__));
                }
            } else {
                @trigger_error(
                    sprintf(
                        'Method %s() will expect the third parameter to be a LocatorInterface in version 2.0. Defining dataroots instead is deprecated since version 1.9.0',
                        __METHOD__
                    ),
                    E_USER_DEPRECATED
                );

                $this->locator = new FileSystemLocator((array) $locatorOrDataRoots);
            }
        } elseif ($locatorOrDataRoots instanceof LocatorInterface) {
            $this->locator = $locatorOrDataRoots;
        } else {
            throw new \InvalidArgumentException(sprintf('Method %s() expects a LocatorInterface for the third argument.', __METHOD__));
        }
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
