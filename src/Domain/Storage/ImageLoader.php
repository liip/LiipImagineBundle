<?php

namespace Liip\ImagineBundle\Domain\Storage;

use Liip\ImagineBundle\Domain\Storage\ImageNotFoundException;
use Liip\ImagineBundle\Domain\ImageReference;

interface ImageLoader
{
    /**
     * @throws ImageNotFoundException
     */
    public function loadImage(string $imageId): ImageReference;
}
