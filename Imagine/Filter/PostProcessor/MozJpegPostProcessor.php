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
 * mozjpeg post-processor, for noticably better jpeg compression.
 *
 * @see http://calendar.perfplanet.com/2014/mozjpeg-3-0/
 * @see https://mozjpeg.codelove.de/binaries.html
 *
 * @author Alex Wilson <a@ax.gy>
 */
class MozJpegPostProcessor implements PostProcessorInterface, ConfigurablePostProcessorInterface
{
    /** @var string Path to the mozjpeg cjpeg binary */
    protected $mozjpegBin;

    /** @var null|int Quality factor */
    protected $quality;

    /**
     * Constructor.
     *
     * @param string   $mozjpegBin Path to the mozjpeg cjpeg binary
     * @param int|null $quality    Quality factor
     */
    public function __construct(
        $mozjpegBin = '/opt/mozjpeg/bin/cjpeg',
        $quality = null
    ) {
        $this->mozjpegBin = $mozjpegBin;
        $this->setQuality($quality);
    }

    /**
     * @param int $quality
     *
     * @return MozJpegPostProcessor
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;

        return $this;
    }

    /**
     * @param BinaryInterface $binary
     *
     * @uses MozJpegPostProcessor::processWithConfiguration
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
        if (!in_array($type, array('image/jpeg', 'image/jpg'))) {
            return $binary;
        }

        $pb = new ProcessBuilder(array($this->mozjpegBin));

        // Places emphasis on DC
        $pb->add('-quant-table');
        $pb->add(2);

        $transformQuality = array_key_exists('quality', $options) ? $options['quality'] : $this->quality;
        if ($transformQuality !== null) {
            $pb->add('-quality');
            $pb->add($transformQuality);
        }

        $pb->add('-optimise');

        // Favor stdin/stdout so we don't waste time creating a new file.
        $pb->setInput($binary->getContent());

        $proc = $pb->getProcess();
        $proc->run();

        if (false !== strpos($proc->getOutput(), 'ERROR') || 0 !== $proc->getExitCode()) {
            throw new ProcessFailedException($proc);
        }

        $result = new Binary($proc->getOutput(), $binary->getMimeType(), $binary->getFormat());

        return $result;
    }
}
