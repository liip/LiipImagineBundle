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
use Symfony\Component\Process\ProcessBuilder;

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

    /**
     * @param BinaryInterface $binary
     * @param array           $options
     *
     * @throws ProcessFailedException
     *
     * @return BinaryInterface|Binary
     */
    protected function doProcess(BinaryInterface $binary, array $options = array())
    {
        if (!$this->isBinaryTypePngImage($binary)) {
            return $binary;
        }

        $file = $this->writeTemporaryFile($binary, $options, 'imagine-post-processor-optipng');

        $process = $this->setupProcessBuilder($options)->add($file)->getProcess();
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
     * @param array $options
     *
     * @return ProcessBuilder
     */
    private function setupProcessBuilder(array $options = array())
    {
        $builder = $this->createProcessBuilder(array($this->executablePath), $options);

        if (null !== $level = isset($options['level']) ? $options['level'] : $this->level) {
            if (!in_array($level, range(0, 7))) {
                throw new InvalidOptionException('the "level" option must be an int between 0 and 7', $options);
            }

            $builder->add(sprintf('-o%d', $level));
        }

        if (isset($options['strip_all'])) {
            @trigger_error(sprintf('The "strip_all" option was deprecated in 1.10.0 and will be removed in 2.0. '.
                'Instead, use the "strip" option.'), E_USER_DEPRECATED);

            if (isset($options['strip'])) {
                throw new InvalidOptionException('the "strip" and "strip_all" options cannot both be set', $options);
            }

            $options['strip'] = $options['strip_all'];
        }

        if ($strip = isset($options['strip']) ? $options['strip'] : $this->strip) {
            $builder->add('-strip')->add(true === $strip ? 'all' : $strip);
        }

        if (isset($options['snip']) && true === $options['snip']) {
            $builder->add('-snip');
        }

        if (isset($options['preserve_attributes']) && true === $options['preserve_attributes']) {
            $builder->add('-preserve');
        }

        if (isset($options['interlace_type'])) {
            if (!in_array($options['interlace_type'], range(0, 1))) {
                throw new InvalidOptionException('the "interlace_type" option must be either 0 or 1', $options);
            }

            $builder->add('-i')->add($options['interlace_type']);
        }

        if (isset($options['no_bit_depth_reductions']) && true === $options['no_bit_depth_reductions']) {
            $builder->add('-nb');
        }

        if (isset($options['no_color_type_reductions']) && true === $options['no_color_type_reductions']) {
            $builder->add('-nc');
        }

        if (isset($options['no_palette_reductions']) && true === $options['no_palette_reductions']) {
            $builder->add('-np');
        }

        if (isset($options['no_reductions']) && true === $options['no_reductions']) {
            $builder->add('-nx');
        }

        return $builder;
    }
}
