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
 * @deprecated This interface was deprecated in 1.10.0 and will be removed in 2.0. Use PostProcessorInterface::process().
 *
 * @author Alex Wilson <a@ax.gy>
 */
interface ConfigurablePostProcessorInterface
{
    /**
     * Performs post-process operation on passed binary and returns the resulting binary.
     *
     * @deprecated This interface was deprecated in 1.10.0 and will be removed in 2.0. Use PostProcessorInterface::process().
     *
     * @param BinaryInterface $binary
     * @param array           $options Operation-specific options
     *
     * @return BinaryInterface
     */
    public function processWithConfiguration(BinaryInterface $binary, array $options);
}
