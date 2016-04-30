<?php

namespace Liip\ImagineBundle\Imagine\Filter\PostProcessor;

use Liip\ImagineBundle\Binary\BinaryInterface;

/**
 * Interface to make PostProcessors configurable without breaking BC.
 *
 * @see PostProcessorInterface for the original interface.
 *
 * @author Alex Wilson <a@ax.gy>
 */
interface ConfigurablePostProcessorInterface
{
    /**
     * Allows processing a BinaryInterface, with run-time options, so PostProcessors remain stateless.
     *
     * @param BinaryInterface $binary
     * @param array           $options Operation-specific options
     *
     * @return BinaryInterface
     */
    public function processWithConfiguration(BinaryInterface $binary, array $options);
}
