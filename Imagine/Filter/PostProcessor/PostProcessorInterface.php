<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Filter\PostProcessor;

use Liip\ImagineBundle\Binary\BinaryInterface;

/**
 * @author Konstantin Tjuterev <kostik.lv@gmail.com>
 * Interface for PostProcessors - handlers which can operate on binaries prepared in FilterManager.
 */
interface PostProcessorInterface
{
    /**
     * Allows processing a BinaryInterface, with run-time options, so PostProcessors remain stateless.
     *
     * @param array $options Operation-specific options
     */
    public function process(BinaryInterface $binary, array $options = []): BinaryInterface;
}
