<?php

namespace Liip\ImagineBundle\Imagine\Filter\PostProcessor;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Binary\FileBinaryInterface;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;

class OptiPngPostProcessor implements PostProcessorInterface
{
    /**
     * @var string Path to optipng binary
     */
    protected $optipngBin;

    /**
     * If set --oN will be passed to optipng.
     *
     * @var int
     */
    protected $level;

    /**
     * If set --strip=all will be passed to optipng.
     *
     * @var bool
     */
    protected $stripAll;

    /**
     * Constructor.
     *
     * @param string $optipngBin Path to the optipng binary
     * @param int    $level      Optimization level
     * @param bool   $stripAll   Strip metadata objects
     */
    public function __construct($optipngBin = '/usr/bin/optipng', $level = 7, $stripAll = true)
    {
        $this->optipngBin = $optipngBin;
        $this->level = $level;
        $this->stripAll = $stripAll;
    }

    /**
     * @param BinaryInterface $binary
     *
     * @throws ProcessFailedException
     *
     * @return BinaryInterface
     *
     * @see      Implementation taken from Assetic\Filter\optipngFilter
     */
    public function process(BinaryInterface $binary)
    {
        $type = strtolower($binary->getMimeType());
        if (!in_array($type, array('image/png'))) {
            return $binary;
        }

        if (false === $input = tempnam(sys_get_temp_dir(), 'imagine_optipng')) {
            throw new \RuntimeException(sprintf('Temp file can not be created in "%s".', sys_get_temp_dir()));
        }

        $pb = new ProcessBuilder(array($this->optipngBin));

        if ($this->level !== null) {
            $pb->add(sprintf('--o%d', $this->level));
        }

        if ($this->stripAll) {
            $pb->add('--strip=all');
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
