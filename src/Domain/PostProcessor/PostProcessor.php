<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Domain\PostProcessor;

use Liip\ImagineBundle\Domain\ImageReference;

/**
 * Post processors do additional work on the resulting image after filters have been applied.
 *
 * @author Konstantin Tjuterev <kostik.lv@gmail.com>
 */
interface PostProcessor
{
    /**
     * Allows processing a BinaryInterface, with run-time options, so PostProcessors remain stateless.
     *
     * @param array<mixed> $options Operation-specific options
     */
    public function process(ImageReference $binary, array $options = []): ImageReference;
}
