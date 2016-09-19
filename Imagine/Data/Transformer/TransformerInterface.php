<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Data\Transformer;

/**
 * @deprecated Will be removed in 2.0
 */
interface TransformerInterface
{
    /**
     * Apply the transformer on the absolute path and return an altered version of it.
     *
     * @param string $absolutePath
     *
     * @return string
     */
    public function apply($absolutePath);
}
