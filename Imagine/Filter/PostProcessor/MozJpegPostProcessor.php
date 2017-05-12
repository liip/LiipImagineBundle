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

    /** @var null|int quantization table */
    protected $quantTable;

    /**
     * Constructor.
     *
     * @param string   $mozjpegBin Path to the mozjpeg cjpeg binary
     * @param int|null $quality    Quality factor
     * @param int|null $quantTable    Quantization table
     */
    public function __construct(
        $mozjpegBin = '/opt/mozjpeg/bin/cjpeg',
        $quality = null,
        $quantTable = 2
    ) {
        $this->mozjpegBin = $mozjpegBin;
        $this->setQuality($quality);
        $this->setQuantTable($quantTable);
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
     * @param int $quantTable
     *
     * @return MozJpegPostProcessor
     */
    public function setQuantTable($quantTable)
    {
        $this->quantTable = $quantTable;

        return $this;
    }

    /**
     * @param BinaryInterface $binary
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
        $quantTable = array_key_exists('quant_table', $options) ? $options['quant_table'] : $this->quantTable;
        if (null !== $quantTable) {
            $pb->add('-quant-table');
            $pb->add($quantTable);
        }


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
