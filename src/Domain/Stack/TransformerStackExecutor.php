<?php

namespace Liip\ImagineBundle\Domain\Stack;

use Liip\ImagineBundle\Domain\ImageReference;

interface TransformerStackExecutor
{
    /**
     * Returns a filter stack for the given stack name.
     *
     * @throws TransformerStackNotFoundException
     */
    public function apply(string $stackName, ImageReference $sourceImageReference): ImageReference;
}
