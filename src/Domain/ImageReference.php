<?php

namespace Liip\ImagineBundle\Domain;

interface ImageReference
{
    public function getContent(): string;

    public function getMimeType(): ?string;

    public function getFormat(): ?string;
}
