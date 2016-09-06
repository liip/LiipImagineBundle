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
 * Interface to make PostProcessors configurable without breaking BC.
 *
 * @see PostProcessorInterface for the original interface
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
