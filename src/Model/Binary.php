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

use Liip\ImagineBundle\Binary\BinaryInterface;

class Binary implements BinaryInterface
{
    protected string $content;

    protected ?string $mimeType;

    protected ?string $format;

    public function __construct(string $content, ?string $mimeType, string $format = null)
    {
        $this->content = $content;
        $this->mimeType = $mimeType;
        $this->format = $format;
    }

    public function getContent(): string
    {
        return $this->content;
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
