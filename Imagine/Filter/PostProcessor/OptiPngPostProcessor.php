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
use Liip\ImagineBundle\Binary\FileBinaryInterface;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

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
     * Directory where temporary file will be written.
     *
     * @var string
     */
    protected $tempDir;

    /**
     * Constructor.
     *
     * @param string $optipngBin Path to the optipng binary
     * @param int    $level      Optimization level
     * @param bool   $stripAll   Strip metadata objects
     * @param string $tempDir    Directory where temporary file will be written
     */
    public function __construct($optipngBin = '/usr/bin/optipng', $level = 7, $stripAll = true, $tempDir = '')
    {
        $this->optipngBin = $optipngBin;
        $this->level = $level;
        $this->stripAll = $stripAll;
        $this->tempDir = $tempDir ?: sys_get_temp_dir();
    }

    /**
     * @param BinaryInterface $binary
     * @param array           $options
     *
     * @throws ProcessFailedException
     *
     * @return BinaryInterface|Binary
     */
    public function process(BinaryInterface $binary, array $options = []): BinaryInterface
    {
        $type = mb_strtolower($binary->getMimeType());
        if (!in_array($type, ['image/png'], true)) {
            return $binary;
        }

        $tempDir = array_key_exists('temp_dir', $options) ? $options['temp_dir'] : $this->tempDir;
        if (false === $input = tempnam($tempDir, 'imagine_optipng')) {
            throw new \RuntimeException(sprintf('Temp file can not be created in "%s".', $tempDir));
        }

        $processArguments = [$this->optipngBin];

        $level = array_key_exists('level', $options) ? $options['level'] : $this->level;
        if (null !== $level) {
            $processArguments[] = sprintf('--o%d', $level);
        }

        $stripAll = array_key_exists('strip_all', $options) ? $options['strip_all'] : $this->stripAll;
        if ($stripAll) {
            $processArguments[] = '--strip=all';
        }

        $processArguments[] = $input;

        if ($binary instanceof FileBinaryInterface) {
            copy($binary->getPath(), $input);
        } else {
            file_put_contents($input, $binary->getContent());
        }

        $proc = new Process($processArguments);
        $proc->run();

        if (false !== mb_strpos($proc->getOutput(), 'ERROR') || 0 !== $proc->getExitCode()) {
            unlink($input);
            throw new ProcessFailedException($proc);
        }

        $result = new Binary(file_get_contents($input), $binary->getMimeType(), $binary->getFormat());

        unlink($input);

        return $result;
    }
}
