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

/**
 * mozjpeg post-processor, for noticeably better jpeg compression.
 *
 * @see http://calendar.perfplanet.com/2014/mozjpeg-3-0/
 * @see https://mozjpeg.codelove.de/binaries.html
 *
 * @author Alex Wilson <a@ax.gy>
 */
class MozJpegPostProcessor extends AbstractPostProcessor
{
    /**
     * @var int|null Quality factor
     */
    protected $quality;

    /**
     * @param string   $executablePath Path to the mozjpeg cjpeg binary
     * @param int|null $quality        Quality factor
     */
    public function __construct($executablePath = '/opt/mozjpeg/bin/cjpeg', $quality = null)
    {
        parent::__construct($executablePath);

        $this->quality = $quality;
    }

    /**
     * @deprecated All post-processor setters have been deprecated in 2.2 for removal in 3.0. You must only use the
     *             class's constructor to set the property state.
     *
     * @param int $quality
     *
     * @return MozJpegPostProcessor
     */
    public function setQuality($quality)
    {
        $this->triggerSetterMethodDeprecation(__METHOD__);
        $this->quality = $quality;

        return $this;
    }

    /*
     * @throws ProcessFailedException
     */
    public function process(BinaryInterface $binary, array $options = []): BinaryInterface
    {
        if (!$this->isBinaryTypeJpgImage($binary)) {
            return $binary;
        }

        $arguments = $this->getProcessArguments($options);
        $process = $this->createProcess($arguments, $options);
        $process->setInput($binary->getContent());
        $process->run();

        if (!$this->isSuccessfulProcess($process)) {
            throw new ProcessFailedException($process);
        }

        return new Binary($process->getOutput(), $binary->getMimeType(), $binary->getFormat());
    }

    /**
     * @param string[] $options
     *
     * @return string[]
     */
    private function getProcessArguments(array $options = []): array
    {
        $arguments = [$this->executablePath];

        if ($quantTable = $options['quant_table'] ?? 2) {
            $arguments[] = '-quant-table';
            $arguments[] = $quantTable;
        }

        if ($options['optimise'] ?? true) {
            $arguments[] = '-optimise';
        }

        if (null !== $quality = $options['quality'] ?? $this->quality) {
            $arguments[] = '-quality';
            $arguments[] = $quality;
        }

        return $arguments;
    }
}
