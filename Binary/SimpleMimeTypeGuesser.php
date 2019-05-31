<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Binary;

use Symfony\Component\Mime\MimeTypesInterface as SymfonyMimeTypeGuesserInterface;

class SimpleMimeTypeGuesser implements MimeTypeGuesserInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface
     */
    protected $mimeTypeGuesser;

    /**
     * @param SymfonyMimeTypeGuesserInterface $mimeTypeGuesser
     */
    public function __construct(SymfonyMimeTypeGuesserInterface $mimeTypeGuesser)
    {
        $this->mimeTypeGuesser = $mimeTypeGuesser;
    }

    public function getExtensions(string $mimeType): array {
      return $this->mimeTypeGuesser->getExtensions($mimeType);
    }

    public function guessMimeType(string $path): ?string {
      return $this->mimeTypeGuesser->guessMimeType($path);
    }


    public function getMimeTypes(string $ext): array {
      return $this->mimeTypeGuesser->getMimeTypes($ext);
    }

    public function isGuesserSupported(): bool {
      return $this->mimeTypeGuesser->isGuesserSupported();
    }



    /**
     * {@inheritdoc}
     */
    public function guess($binary)
    {
        if (false === $tmpFile = tempnam(sys_get_temp_dir(), 'liip-imagine-bundle')) {
            throw new \RuntimeException(sprintf('Temp file can not be created in "%s".', sys_get_temp_dir()));
        }

        try {
            file_put_contents($tmpFile, $binary);

            $mimeType = $this->mimeTypeGuesser->guessMimeType($tmpFile);

            unlink($tmpFile);

            return $mimeType;
        } catch (\Exception $e) {
            unlink($tmpFile);

            throw $e;
        }
    }
}
