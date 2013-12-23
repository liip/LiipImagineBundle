<?php
namespace Liip\ImagineBundle\Imagine;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

class SimpleMimeTypeGuesser implements MimeTypeGuesserInterface
{
    /**
     * {@inheritDoc}
     */
    public function guess($binary)
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'liip-imagine-bundle');

        try {
            file_put_contents($tmpFile, $binary);

            $mimeType = MimeTypeGuesser::getInstance()->guess($tmpFile);

            unlink($tmpFile);

            return $mimeType;
        } catch (\Exception $e) {
            unlink($tmpFile);

            throw $e;
        }
    }
}
