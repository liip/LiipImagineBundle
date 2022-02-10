<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Model;

use Liip\ImagineBundle\Binary\FileBinaryInterface;

class FileBinary implements FileBinaryInterface
{
    protected string $path;

    protected ?string $mimeType;

    protected ?string $format;

    public function __construct(string $path, ?string $mimeType, ?string $format = null)
    {
        $this->path = $path;
        $this->mimeType = $mimeType;
        $this->format = $format;
    }

    public function getContent(): string
    {
        $content = file_get_contents($this->path);
        if (false === $content) {
            $error = error_get_last();

            throw new \ErrorException($error['message'] ?? 'An error occured', 0, $error['type'] ?? 1);
        }

        return $content;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }
}
