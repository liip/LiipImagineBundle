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
 * Interface for PostProcessors - handlers which can operate on binaries prepared in FilterManager.
 *
 * @see ConfigurablePostProcessorInterface For a means to configure these at run-time
 *
 * @author Konstantin Tjuterev <kostik.lv@gmail.com>
 */
interface PostProcessorInterface
{
    /**
     * @param BinaryInterface $binary
     *
     * @return BinaryInterface
     */
    public function process(BinaryInterface $binary);
}
