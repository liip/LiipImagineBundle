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
     * This method will continue to support two prior, deprecated signitures for the duration of the 1.x
     * release. The currently documented signiture will be the only valid usage once 2.0 is release. You
     * can reference PR-963 {@see https://github.com/liip/LiipImagineBundle/pull/963} for more information.
     *
     * @param MimeTypeGuesserInterface  $mimeGuesser
     * @param ExtensionGuesserInterface $extensionGuesser
     * @param LocatorInterface          $locator
     */
    public function __construct(MimeTypeGuesserInterface $mimeGuesser, ExtensionGuesserInterface $extensionGuesser, $locator)
    {
        $this->mimeTypeGuesser = $mimeGuesser;
        $this->extensionGuesser = $extensionGuesser;

        if ($locator instanceof LocatorInterface) { // post-1.9.0 behavior
            $this->locator = $locator;
        } elseif (is_array($locator) || is_string($locator)) { // pre-1.9.0 behaviour
            if (count((array) $locator) === 0) {
                throw new InvalidArgumentException('One or more data root paths must be specified.');
            }

            if (func_num_args() >= 4) {
                if (func_get_arg(3) instanceof LocatorInterface) {
                    @trigger_error(sprintf(
                        'Passing a LocatorInterface as fourth parameter to %s() is deprecated. It needs to be the '.
                        'third parameter. The previous third parameter (data roots) is removed and the data roots must '.
                        'now be passed as a constructor argument to the LocatorInterface passed to this method.', __METHOD__
                    ), E_USER_DEPRECATED);

                    $this->locator = func_get_arg(3);
                    $this->locator->setOptions(array('roots' => (array) $locator));
                } else {
                    throw new \InvalidArgumentException(sprintf(
                        'Unknown call to %s(). Please check the method signature.', __METHOD__
                    ));
                }
            } else {
                @trigger_error(sprintf(
                    'Method %s() will expect the third parameter to be a LocatorInterface in version 2.0. Defining '.
                    'data roots instead is deprecated since version 1.9.0', __METHOD__
                ), E_USER_DEPRECATED);

                $this->locator = new FileSystemLocator((array) $locator);
            }
        } else { // invalid behavior
            throw new \InvalidArgumentException(sprintf(
                'Method %s() expects a LocatorInterface for the third argument.', __METHOD__
            ));
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
