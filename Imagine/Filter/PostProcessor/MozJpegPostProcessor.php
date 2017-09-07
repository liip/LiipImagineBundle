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
class MozJpegPostProcessor extends AbstractPostProcessor
{
    /**
     * @var null|int Quality factor
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
     * @deprecated All post-processor setters have been deprecated in 1.10.0 for removal in 2.0. You must only use the
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

    /**
     * @param BinaryInterface $binary
     * @param array           $options
     *
     * @throws ProcessFailedException
     *
     * @return BinaryInterface
     */
    protected function doProcess(BinaryInterface $binary, array $options = array())
    {
        if (!$this->isBinaryTypeJpgImage($binary)) {
            return $binary;
        }

        $process = $this->setupProcessBuilder($options, $binary)->setInput($binary->getContent())->getProcess();
        $process->run();

        if (!$this->isSuccessfulProcess($process)) {
            throw new ProcessFailedException($process);
        }

        return new Binary($process->getOutput(), $binary->getMimeType(), $binary->getFormat());
    }

    /**
     * @param array $options
     *
     * @return ProcessBuilder
     */
    private function setupProcessBuilder(array $options = array())
    {
        $builder = $this->createProcessBuilder(array($this->executablePath), $options);

        if ($quantTable = isset($options['quant_table']) ? $options['quant_table'] : 2) {
            $builder->add('-quant-table')->add($quantTable);
        }

        if (isset($options['optimise']) ? $options['optimise'] : true) {
            $builder->add('-optimise');
        }

        if (null !== $quality = isset($options['quality']) ? $options['quality'] : $this->quality) {
            $builder->add('-quality')->add($quality);
        }

        return $builder;
    }
}
