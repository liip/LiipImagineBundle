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
use Liip\ImagineBundle\Exception\Imagine\Filter\PostProcessor\InvalidOptionException;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\Process\Exception\ProcessFailedException;

class OptiPngPostProcessor extends AbstractPostProcessor
{
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
    protected $strip;

    /**
     * @param string $executablePath    Path to the optipng binary
     * @param int    $level             Optimization level
     * @param bool   $strip             Strip metadata objects
     * @param string $temporaryRootPath Directory where temporary file will be written
     */
    public function __construct($executablePath = '/usr/bin/optipng', $level = 7, $strip = true, $temporaryRootPath = null)
    {
        parent::__construct($executablePath, $temporaryRootPath);

        $this->level = $level;
        $this->strip = $strip;
    }

    /*
     * @throws ProcessFailedException
     */
    public function process(BinaryInterface $binary, array $options = []): BinaryInterface
    {
        if (!$this->isBinaryTypePngImage($binary)) {
            return $binary;
        }

        $file = $this->writeTemporaryFile($binary, $options, 'imagine-post-processor-optipng');

        $arguments = $this->getProcessArguments($options);
        $arguments[] = $file;
        $process = $this->createProcess($arguments, $options);
        $process->run();

        if (!$this->isSuccessfulProcess($process)) {
            unlink($file);
            throw new ProcessFailedException($process);
        }

        $result = new Binary(file_get_contents($file), $binary->getMimeType(), $binary->getFormat());

        unlink($file);

        return $result;
    }

    /**
     * @param string[] $options
     *
     * @return string[]
     */
    private function getProcessArguments(array $options = []): array
    {
        $arguments = [$this->executablePath];

        if (null !== $level = $options['level'] ?? $this->level) {
            if (!\in_array($level, range(0, 7), true)) {
                throw new InvalidOptionException('the "level" option must be an int between 0 and 7', $options);
            }

            $arguments[] = sprintf('-o%d', $level);
        }

        if (isset($options['strip_all'])) {
            @trigger_error('The "strip_all" option was deprecated in 2.2 and will be removed in 3.0. '.
                'Instead, use the "strip" option.', E_USER_DEPRECATED);

            if (isset($options['strip'])) {
                throw new InvalidOptionException('the "strip" and "strip_all" options cannot both be set', $options);
            }

            $options['strip'] = $options['strip_all'];
        }

        if ($strip = $options['strip'] ?? $this->strip) {
            $arguments[] = '-strip';
            $arguments[] = true === $strip ? 'all' : $strip;
        }

        if (isset($options['snip']) && true === $options['snip']) {
            $arguments[] = '-snip';
        }

        if (isset($options['preserve_attributes']) && true === $options['preserve_attributes']) {
            $arguments[] = '-preserve';
        }

        if (isset($options['interlace_type'])) {
            if (!\in_array($options['interlace_type'], range(0, 1), true)) {
                throw new InvalidOptionException('the "interlace_type" option must be either 0 or 1', $options);
            }

            $arguments[] = '-i';
            $arguments[] = $options['interlace_type'];
        }

        if (isset($options['no_bit_depth_reductions']) && true === $options['no_bit_depth_reductions']) {
            $arguments[] = '-nb';
        }

        if (isset($options['no_color_type_reductions']) && true === $options['no_color_type_reductions']) {
            $arguments[] = '-nc';
        }

        if (isset($options['no_palette_reductions']) && true === $options['no_palette_reductions']) {
            $arguments[] = '-np';
        }

        if (isset($options['no_reductions']) && true === $options['no_reductions']) {
            $arguments[] = '-nx';
        }

        return $arguments;
    }
}
