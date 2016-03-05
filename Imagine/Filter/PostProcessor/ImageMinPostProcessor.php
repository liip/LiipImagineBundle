<?php

namespace Liip\ImagineBundle\Imagine\Filter\PostProcessor;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;

class ImageMinPostProcessor implements PostProcessorInterface
{
    /** @var string Path to imagemin-cli binary */
    protected $imageminBin;

    /**
     * If set, --optimizationLevel $value will be passed to imagemin
     *
     * @var int
     */
    protected $optimizationLevel;

    /**
     * If set to true --progressive will be passed to imagemin
     *
     * @var bool
     */
    protected $progressive;


    /**
     * If set to true --interlaced will be passed to imagemin
     *
     * @var bool
     */
    protected $interlaced;

    /**
     * Constructor.
     *
     * @param string $imageminBin Path to the jpegoptim binary
     */
    public function __construct($imageminBin = '/usr/local/bin/imagemin')
    {
        $this->imageminBin = $imageminBin;
    }

    /**
     * @param int $optimizationLevel
     *
     * @return ImageMinPostProcessor
     */
    public function setOptimizationLevel($optimizationLevel)
    {
        $this->optimizationLevel = $optimizationLevel;

        return $this;
    }

    /**
     * @param boolean $progressive
     *
     * @return ImageMinPostProcessor
     */
    public function setProgressive($progressive)
    {
        $this->progressive = $progressive;

        return $this;
    }

    /**
     * @param $interlaced
     *
     * @return ImageMinPostProcessor
     */
    public function setInterlaced($interlaced)
    {
        $this->interlaced = $interlaced;

        return $this;
    }


    /**
     * @param BinaryInterface $binary
     *
     * @throws ProcessFailedException
     *
     * @return BinaryInterface
     *
     * @see      Implementation taken from Assetic\Filter\JpegoptimFilter
     */
    public function process(BinaryInterface $binary)
    {
        $type = strtolower($binary->getMimeType());
        if (!in_array($type, array('image/png'))) {
            return $binary;
        }

        $pb = new ProcessBuilder(array($this->imageminBin));

        if ($this->optimizationLevel) {
            $pb->add('--optimizationLevel='.$this->optimizationLevel);
        }

        if ($this->progressive) {
            $pb->add('--progressive');
        }

        if ($this->interlaced) {
            $pb->add('--interlaced');
        }

//        $pb->add($input = tempnam(sys_get_temp_dir(), 'imagine_imagemin'));
//        file_put_contents($input, $binary->getContent());

        $proc = $pb->getProcess();
        $proc->setInput($binary->getContent());
        $proc->run();

        if (!$proc->isSuccessful()) {
            throw new ProcessFailedException($proc);
        }

        $result = new Binary($proc->getOutput(), $binary->getMimeType(), $binary->getFormat());

        return $result;
    }
}