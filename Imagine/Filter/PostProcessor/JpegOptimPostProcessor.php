<?php

namespace Liip\ImagineBundle\Imagine\Filter\PostProcessor;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Binary\FileBinaryInterface;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;

class JpegOptimPostProcessor implements PostProcessorInterface, ConfigurablePostProcessorInterface
{
    /** @var string Path to jpegoptim binary */
    protected $jpegoptimBin;

    /**
     * If set --strip-all will be passed to jpegoptim.
     *
     * @var bool
     */
    protected $stripAll;

    /**
     * If set, --max=$value will be passed to jpegoptim.
     *
     * @var int
     */
    protected $max;

    /**
     * If set to true --all-progressive will be passed to jpegoptim, otherwise --all-normal will be passed.
     *
     * @var bool
     */
    protected $progressive;

    /**
     * Constructor.
     *
     * @param string $jpegoptimBin Path to the jpegoptim binary
     * @param bool   $stripAll     Strip all markers from output
     * @param int    $max          Set maximum image quality factor
     * @param bool   $progressive  Force output to be progressive
     */
    public function __construct($jpegoptimBin = '/usr/bin/jpegoptim', $stripAll = true, $max = null, $progressive = true)
    {
        $this->jpegoptimBin = $jpegoptimBin;
        $this->stripAll = $stripAll;
        $this->max = $max;
        $this->progressive = $progressive;
    }

    /**
     * @param int $max
     *
     * @return JpegOptimPostProcessor
     */
    public function setMax($max)
    {
        $this->max = $max;

        return $this;
    }

    /**
     * @param bool $progressive
     *
     * @return JpegOptimPostProcessor
     */
    public function setProgressive($progressive)
    {
        $this->progressive = $progressive;

        return $this;
    }

    /**
     * @param bool $stripAll
     *
     * @return JpegOptimPostProcessor
     */
    public function setStripAll($stripAll)
    {
        $this->stripAll = $stripAll;

        return $this;
    }

    /**
     * @param BinaryInterface $binary
     *
     * @uses JpegOptimPostProcessor::processWithConfiguration
     *
     * @throws ProcessFailedException
     *
     * @return BinaryInterface
     *
     * @see Implementation taken from Assetic\Filter\JpegoptimFilter
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
     *
     * @see Implementation taken from Assetic\Filter\JpegoptimFilter
     */
    public function processWithConfiguration(BinaryInterface $binary, array $options)
    {
        $type = strtolower($binary->getMimeType());
        if (!in_array($type, array('image/jpeg', 'image/jpg'))) {
            return $binary;
        }

        if (false === $input = tempnam(sys_get_temp_dir(), 'imagine_jpegoptim')) {
            throw new \RuntimeException(sprintf('Temp file can not be created in "%s".', sys_get_temp_dir()));
        }

        $pb = new ProcessBuilder(array($this->jpegoptimBin));

        $stripAll = array_key_exists('strip_all', $options) ? $options['strip_all'] : $this->stripAll;
        if ($stripAll) {
            $pb->add('--strip-all');
        }

        $max = array_key_exists('max', $options) ? $options['max'] : $this->max;
        if ($max) {
            $pb->add('--max='.$max);
        }

        $progressive = array_key_exists('progressive', $options) ? $options['progressive'] : $this->progressive;
        if ($progressive) {
            $pb->add('--all-progressive');
        } else {
            $pb->add('--all-normal');
        }

        $pb->add($input);
        if ($binary instanceof FileBinaryInterface) {
            copy($binary->getPath(), $input);
        } else {
            file_put_contents($input, $binary->getContent());
        }

        $proc = $pb->getProcess();
        $proc->run();

        if (false !== strpos($proc->getOutput(), 'ERROR') || 0 !== $proc->getExitCode()) {
            unlink($input);
            throw new ProcessFailedException($proc);
        }

        $result = new Binary(file_get_contents($input), $binary->getMimeType(), $binary->getFormat());

        unlink($input);

        return $result;
    }
}
