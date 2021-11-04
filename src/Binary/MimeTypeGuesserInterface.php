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

interface MimeTypeGuesserInterface
{
    /**
     * @param string $binary The image binary
     *
     * @return string|null mime type or null if it could be not be guessed
     */
    public function guess($binary);
}
