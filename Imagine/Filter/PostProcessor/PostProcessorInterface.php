<?php

namespace Liip\ImagineBundle\Imagine\Filter\PostProcessor;

use Liip\ImagineBundle\Binary\BinaryInterface;

/**
 * Interface for PostProcessors - handlers which can operate on binaries prepared in FilterManager.
 *
 * @see ConfigurablePostProcessorInterface For a means to configure these at run-time.
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
