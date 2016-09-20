<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

/**
 * Downscale filter.
 *
 * @author Devi Prasad <https://github.com/deviprsd21>
 */
class DownscaleFilterLoader extends ScaleFilterLoader
{
    public function __construct()
    {
        parent::__construct('max', 'by', false);
    }

    protected function calcAbsoluteRatio($ratio)
    {
        return 1 - ($ratio > 1 ? $ratio - floor($ratio) : $ratio);
    }

    protected function isImageProcessable($ratio)
    {
        return $ratio < 1;
    }
}
