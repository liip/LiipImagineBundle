<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Config\Controller;

use Liip\ImagineBundle\Exception\InvalidArgumentException;

final class ControllerConfig
{
    public const REDIRECT_RESPONSE_CODES = [201, 301, 302, 303, 307, 308];

    private $redirectResponseCode;

    public function __construct(int $redirectResponseCode)
    {
        if (!\in_array($redirectResponseCode, self::REDIRECT_RESPONSE_CODES, true)) {
            throw new InvalidArgumentException(sprintf('Invalid redirect response code "%s" (must be 201, 301, 302, 303, 307, or 308).', $redirectResponseCode));
        }

        $this->redirectResponseCode = $redirectResponseCode;
    }

    public function getRedirectResponseCode(): int
    {
        return $this->redirectResponseCode;
    }
}
