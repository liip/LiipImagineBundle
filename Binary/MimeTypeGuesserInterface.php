<?php

namespace Liip\ImagineBundle\Binary;

interface MimeTypeGuesserInterface
{
    /**
     * @param string $binary The image binary
     *
     * @return string|null mime type or null if it could be not be guessed.
     */
    public function guess($binary);
}
