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
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;

/**
 * pngquant post-processor, for optimal, web-safe, lossy png compression
 * This requires a recent version of pngquant (so 2.3 or higher?)
 * See pngqaunt.org if you are unable to find a binary package for your distribution.
 *
 * @see https://pngquant.org/
 *
 * @author Alex Wilson <a@ax.gy>
 */
class PngquantPostProcessor implements PostProcessorInterface, ConfigurablePostProcessorInterface
{
    /** @var string Path to pngquant binary */
    protected $pngquantBin;

    /** @var string Quality to pass to pngquant */
    protected $quality;

    /**
     * Constructor.
     *
     * @param string $pngquantBin Path to the pngquant binary
     */
    public function __construct($pngquantBin = '/usr/bin/pngquant', $quality = '80-100')
    {
        $this->pngquantBin = $pngquantBin;
        $this->setQuality($quality);
    }

    /**
     * @param string $quality
     *
     * @return PngquantPostProcessor
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;

        return $this;
    }

    /**
     * @param BinaryInterface $binary
     *
     * @uses PngquantPostProcessor::processWithConfiguration
     *
     * @throws ProcessFailedException
     *
     * @return BinaryInterface
     */
    public function process(BinaryInterface $binary)
    {
        return $this->processWithConfiguration($binary, array());
    }

    /**
     * @param BinaryInterface $binary
     * @param array           $options
     *
     * @throws ProcessFailedException
     *
     * @return BinaryInterface
     */
    public function processWithConfiguration(BinaryInterface $binary, array $options)
    {
        $type = strtolower($binary->getMimeType());
        if (!in_array($type, array('image/png'))) {
            return $binary;
        }

        $pb = new ProcessBuilder(array($this->pngquantBin));

        // Specify quality.
        $tranformQuality = array_key_exists('quality', $options) ? $options['quality'] : $this->quality;
        $pb->add('--quality');
        $pb->add($tranformQuality);

        // Read to/from stdout to save resources.
        $pb->add('-');
        $pb->setInput($binary->getContent());

        $proc = $pb->getProcess();
        $proc->run();

        // 98 and 99 are "quality too low" to compress current current image which, while isn't ideal, is not a failure
        if (!in_array($proc->getExitCode(), array(0, 98, 99))) {
            throw new ProcessFailedException($proc);
        }

        $result = new Binary($proc->getOutput(), $binary->getMimeType(), $binary->getFormat());

        return $result;
    }
}
