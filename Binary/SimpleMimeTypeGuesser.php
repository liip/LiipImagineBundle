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

use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface as DeprecatedSymfonyMimeTypeGuesserInterface;
use Symfony\Component\Mime\MimeTypesInterface as SymfonyMimeTypeGuesserInterface;

class SimpleMimeTypeGuesser implements MimeTypeGuesserInterface
{
    /**
     * @var DeprecatedSymfonyMimeTypeGuesserInterface|SymfonyMimeTypeGuesserInterface
     */
    protected $mimeTypeGuesser;

    /**
     * @param DeprecatedSymfonyMimeTypeGuesserInterface|SymfonyMimeTypeGuesserInterface $mimeTypeGuesser
     */
    public function __construct($mimeTypeGuesser)
    {
        if (!$mimeTypeGuesser instanceof SymfonyMimeTypeGuesserInterface && !$mimeTypeGuesser instanceof DeprecatedSymfonyMimeTypeGuesserInterface) {
            throw new InvalidArgumentException('$mimeTypeGuesser must be an instance of Symfony\Component\Mime\MimeTypeGuesserInterface or Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface');
        }

        if (interface_exists((SymfonyMimeTypeGuesserInterface::class) && $mimeTypeGuesser instanceof DeprecatedSymfonyMimeTypeGuesserInterface)) {
            @trigger_error(sprintf('Passing a %s to "%s()" is deprecated since Symfony 4.3, pass a "%s" instead.', DeprecatedSymfonyMimeTypeGuesserInterface::class, __METHOD__, SymfonyMimeTypeGuesserInterface::class), E_USER_DEPRECATED);
        }

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

            $mimeType = interface_exists(SymfonyMimeTypeGuesserInterface::class) ? $this->mimeTypeGuesser->guessMimeType($tmpFile) : $this->mimeTypeGuesser->guess($tmpFile);

            unlink($tmpFile);

            return $mimeType;
        } catch (\Exception $e) {
            unlink($tmpFile);

            throw $e;
        }
    }
}
