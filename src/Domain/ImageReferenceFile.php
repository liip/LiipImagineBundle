<?php

namespace Liip\ImagineBundle\Domain;

interface ImageReferenceFile extends ImageReference
{
    public function getPath(): string;
}
