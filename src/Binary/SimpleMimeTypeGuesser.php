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

use Symfony\Component\Mime\MimeTypesInterface;

class SimpleMimeTypeGuesser implements MimeTypeGuesserInterface
{
    /**
     * @var MimeTypesInterface
     */
    protected $mimeTypeGuesser;

    public function __construct(MimeTypesInterface $mimeTypeGuesser)
    {
        $this->mimeTypeGuesser = $mimeTypeGuesser;
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
