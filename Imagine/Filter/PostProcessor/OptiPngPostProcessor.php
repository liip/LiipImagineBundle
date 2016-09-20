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
use Symfony\Component\Process\ProcessBuilder;

class OptiPngPostProcessor implements PostProcessorInterface, ConfigurablePostProcessorInterface
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
     * @return BinaryInterface|Binary
     *
     * @see    Implementation taken from Assetic\Filter\optipngFilter
     */
    public function processWithConfiguration(BinaryInterface $binary, array $options)
    {
        $type = strtolower($binary->getMimeType());
        if (!in_array($type, array('image/png'))) {
            return $binary;
        }

        $tempDir = array_key_exists('temp_dir', $options) ? $options['temp_dir'] : $this->tempDir;
        if (false === $input = tempnam($tempDir, 'imagine_optipng')) {
            throw new \RuntimeException(sprintf('Temp file can not be created in "%s".', $tempDir));
        }

        $pb = new ProcessBuilder(array($this->optipngBin));

        $level = array_key_exists('level', $options) ? $options['level'] : $this->level;
        if ($level !== null) {
            $pb->add(sprintf('--o%d', $level));
        }

        $stripAll = array_key_exists('strip_all', $options) ? $options['strip_all'] : $this->stripAll;
        if ($stripAll) {
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
