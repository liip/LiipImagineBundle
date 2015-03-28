<?php

namespace Liip\ImagineBundle\Binary;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface as SymfonyMimeTypeGuesserInterface;

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

    /**
     * {@inheritDoc}
     */
    public function guess($binary)
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'liip-imagine-bundle');

        try {
            file_put_contents($tmpFile, $binary);

            $mimeType = $this->mimeTypeGuesser->guess($tmpFile);

            unlink($tmpFile);

            return $mimeType;
        } catch (\Exception $e) {
            unlink($tmpFile);

            throw $e;
        }
    }
}
