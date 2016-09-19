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
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;

class FlysystemLoader implements LoaderInterface
{
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var ExtensionGuesserInterface
     */
    protected $extensionGuesser;

    public function __construct(
        ExtensionGuesserInterface $extensionGuesser,
        FilesystemInterface $filesystem)
    {
        $this->extensionGuesser = $extensionGuesser;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function find($path)
    {
        if ($this->filesystem->has($path) === false) {
            throw new NotLoadableException(sprintf('Source image "%s" not found.', $path));
        }

        $mimeType = $this->filesystem->getMimetype($path);

        return new Binary(
            $this->filesystem->read($path),
            $mimeType,
            $this->extensionGuesser->guess($mimeType)
        );
    }
}
