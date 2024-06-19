<?php

namespace Liip\ImagineBundle\Domain\Stack;

use Liip\ImagineBundle\Domain\ImageReference;
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\PostProcessorInterface;

class LiipTransformerStack implements TransformerStack
{
    /**
     * @param FilterInterface[] $filters # used to be Filter\Loader\LoaderInterface
     * @param PostProcessorInterface[] $postProcessors
     */
    public function __construct(
        private array $filters,
        private array $postProcessors,
    ) {}

    public function applyTo(ImageReference $image): ImageReference
    {
        foreach ($this->filters as $filter) {
            $image = $filter->applyTo($image);
        }

        foreach ($this->postProcessors as $postProcessor) {
            $image = $postProcessor->process($image);
        }

        return $image;
    }
}
