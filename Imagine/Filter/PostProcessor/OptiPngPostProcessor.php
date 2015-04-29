<?php

namespace Liip\ImagineBundle\Imagine\Filter\PostProcessor;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;

class OptiPngPostProcessor implements PostProcessorInterface
{
    const MIN_LEVEL = 0;
    const MAX_LEVEL = 7;

    private static $allowedTypes = array(
        'image/png',
        'image/bmp',
        'image/gif',
        'image/x-windows-bmp',
        'image/x-portable-anymap',
        'image/tiff',
        'image/x-tiff',
    );

    /** @var string Path to optipng binary */
    protected $optipngBin;

    /**
     * If set, --o=$value will be passed to optipng.
     *
     * @var int
     */
    protected $level;

    /**
     * Constructor.
     *
     * @param string $optipngBin Path to the optipng binary
     */
    public function __construct($optipngBin = '/usr/bin/optipng')
    {
        $this->optipngBin = $optipngBin;
    }

    /**
     * @param int $level
     *
     * @return OptiPngPostProcessor
     */
    public function setLevel($level)
    {
        $this->level = $this->sanitizeLevel($level);

        return $this;
    }

    private function sanitizeLevel($level)
    {
        if ($level > self::MAX_LEVEL) {
            return self::MAX_LEVEL;
        }

        if ($level < self::MIN_LEVEL) {
            return self::MIN_LEVEL;
        }

        return $level;
    }

    /**
     * @param BinaryInterface $binary
     *
     * @throws ProcessFailedException
     *
     * @return BinaryInterface
     *
     * @see      Implementation taken from Assetic\Filter\OptiPngFilter
     */
    public function process(BinaryInterface $binary)
    {
        $type = strtolower($binary->getMimeType());
        if (!in_array($type, self::$allowedTypes)) {
            return $binary;
        }

        $pb = new ProcessBuilder(array($this->optipngBin));

        if ($this->level) {
            $pb->add('-o')->add($this->level);
        }

        $pb->add('-out')->add($output = tempnam(sys_get_temp_dir(), 'imagine_optipng_out'));
        unlink($output);

        $pb->add($input = tempnam(sys_get_temp_dir(), 'imagine_optipng_in'));
        file_put_contents($input, $binary->getContent());

        $proc = $pb->getProcess();
        $code = $proc->run();

        if (0 !== $code) {
            unlink($input);
            throw new ProcessFailedException($proc);
        }

        $result = new Binary(file_get_contents($output), $binary->getMimeType(), $binary->getFormat());

        unlink($input);
        unlink($output);

        return $result;
    }
}
