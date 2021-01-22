<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Config;

/**
 * A filter contains the configuration for an image transformation operation.
 *
 * The type of operation is defined by the filter class. The operation parameters are specific to
 * the filter.
 */
interface FilterInterface
{
    /**
     * Filter identifier
     */
    public function getName(): string;
}
