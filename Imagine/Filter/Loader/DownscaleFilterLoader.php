<?php

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

/**
 * Downscale filter.
 *
 * @author Devi Prasad <https://github.com/deviprsd21>
 */
class DownscaleFilterLoader extends ScaleFilterLoader
{
    public function __construct () {
        parent::__construct('max', 'by', false);
    }

    protected function calcAbsoluteRatio ($ratio) {
        return 1 - ($ratio > 1 ? $ratio - floor($ratio) : $ratio);
    }

    protected function isImageProcessable ($ratio) {
        return $ratio < 1;
    }
}
