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
    /**
     * @var int
     */
    private $redirectResponseCode;

    /**
     * @param int $redirectResponseCode
     */
    public function __construct(int $redirectResponseCode)
    {
        if (!in_array($redirectResponseCode, [201, 301, 302, 303, 307, 308], true)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid redirect response code "%s" (must be 201, 301, 302, 303, 307, or 308).', $redirectResponseCode
            ));
        }

        $this->redirectResponseCode = $redirectResponseCode;
    }

    /**
     * @return int
     */
    public function getRedirectResponseCode(): int
    {
        return $this->redirectResponseCode;
    }
}
