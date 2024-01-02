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
     */
    protected int $level;

    /**
     * If set --strip=all will be passed to optipng.
     */
    protected bool $strip;

    /**
     * @param string      $executablePath    Path to the optipng binary
     * @param int         $level             Optimization level
     * @param bool        $strip             Strip metadata objects
     * @param string|null $temporaryRootPath Directory where temporary file will be written
     */
    public function __construct(string $executablePath = '/usr/bin/optipng', int $level = 7, bool $strip = true, string $temporaryRootPath = null)
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
     * @param array<string, string|int|bool> $options
     *
     * @return string[]
     */
    private function getProcessArguments(array $options = []): array
    {
        $arguments = [$this->executablePath];

        if (null !== $level = ($options['level'] ?? $this->level)) {
            if (!\in_array($level, range(0, 7), true)) {
                throw new InvalidOptionException('the "level" option must be an int between 0 and 7', $options);
            }

            $arguments[] = sprintf('-o%d', $level);
        }

        if ($strip = ($options['strip'] ?? $this->strip)) {
            $arguments[] = '-strip';
            $arguments[] = true === $strip ? 'all' : $strip;
        }

        if (true === ($options['snip'] ?? false)) {
            $arguments[] = '-snip';
        }

        if (true === ($options['preserve_attributes'] ?? false)) {
            $arguments[] = '-preserve';
        }

        if (\array_key_exists('interlace_type', $options) && null !== $options['interlace_type']) {
            if (!\in_array($options['interlace_type'], range(0, 1), true)) {
                throw new InvalidOptionException('the "interlace_type" option must be either 0 or 1', $options);
            }

            $arguments[] = '-i';
            $arguments[] = $options['interlace_type'];
        }

        if (true === ($options['no_bit_depth_reductions'] ?? false)) {
            $arguments[] = '-nb';
        }

        if (true === ($options['no_color_type_reductions'] ?? false)) {
            $arguments[] = '-nc';
        }

        if (true === ($options['no_palette_reductions'] ?? false)) {
            $arguments[] = '-np';
        }

        if (true === ($options['no_reductions'] ?? false)) {
            $arguments[] = '-nx';
        }

        return $arguments;
    }
}
