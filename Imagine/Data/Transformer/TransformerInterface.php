<?php

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
