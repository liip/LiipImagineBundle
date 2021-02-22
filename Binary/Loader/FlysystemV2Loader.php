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

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\Mime\MimeTypesInterface;

class FlysystemV2Loader implements LoaderInterface
{
    /**
     * @var FilesystemOperator
     */
    protected $filesystem;

    /**
     * @var MimeTypesInterface
     */
    protected $extensionGuesser;

    public function __construct(
        MimeTypesInterface $extensionGuesser,
        FilesystemOperator $filesystem
    ) {
        $this->extensionGuesser = $extensionGuesser;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function find($path)
    {
        try {
            $mimeType = $this->filesystem->mimeType($path);

            $extension = $this->getExtension($mimeType);

            return new Binary(
                $this->filesystem->read($path),
                $mimeType,
                $extension
            );
        } catch (FilesystemException $exception) {
            throw new NotLoadableException(sprintf('Source image "%s" not found.', $path), 0, $exception);
        }
    }

    private function getExtension(?string $mimeType): ?string
    {
        return $this->extensionGuesser->getExtensions($mimeType)[0] ?? null;
    }
}
