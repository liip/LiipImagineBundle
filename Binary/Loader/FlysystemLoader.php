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

use League\Flysystem\FilesystemInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface as DeprecatedExtensionGuesserInterface;
use Symfony\Component\Mime\MimeTypesInterface;

class FlysystemLoader implements LoaderInterface
{
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var MimeTypesInterface|DeprecatedExtensionGuesserInterface
     */
    protected $extensionGuesser;

    public function __construct(
        $extensionGuesser,
        FilesystemInterface $filesystem)
    {
        if (!$extensionGuesser instanceof MimeTypesInterface && !$extensionGuesser instanceof DeprecatedExtensionGuesserInterface) {
            throw new InvalidArgumentException('$extensionGuesser must be an instance of Symfony\Component\Mime\MimeTypesInterface or Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface');
        }

        if (interface_exists(MimeTypesInterface::class) && $extensionGuesser instanceof DeprecatedExtensionGuesserInterface) {
            @trigger_error(sprintf('Passing a %s to "%s()" is deprecated since Symfony 4.3, pass a "%s" instead.', DeprecatedExtensionGuesserInterface::class, __METHOD__, MimeTypesInterface::class), E_USER_DEPRECATED);
        }

        $this->extensionGuesser = $extensionGuesser;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function find($path)
    {
        if (false === $this->filesystem->has($path)) {
            throw new NotLoadableException(sprintf('Source image "%s" not found.', $path));
        }

        $mimeType = $this->filesystem->getMimetype($path);

        $extension = $this->getExtension($mimeType);

        return new Binary(
            $this->filesystem->read($path),
            $mimeType,
            $extension
        );
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
