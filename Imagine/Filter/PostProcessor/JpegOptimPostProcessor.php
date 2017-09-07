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

class JpegOptimPostProcessor extends AbstractPostProcessor
{
    /**
     * If set --strip-all will be passed to jpegoptim.
     *
     * @var bool
     */
    protected $strip;

    /**
     * If set, --max=$value will be passed to jpegoptim.
     *
     * @var int
     */
    protected $quality;

    /**
     * If set to true --all-progressive will be passed to jpegoptim, otherwise --all-normal will be passed.
     *
     * @var bool
     */
    protected $progressive;

    /**
     * @param string $executablePath    Path to the jpegoptim binary
     * @param bool   $strip             Strip all markers from output
     * @param int    $quality           Set maximum image quality factor
     * @param bool   $progressive       Force output to be progressive
     * @param string $temporaryRootPath Directory where temporary file will be written
     */
    public function __construct($executablePath = '/usr/bin/jpegoptim', $strip = true, $quality = null, $progressive = true, $temporaryRootPath = null)
    {
        parent::__construct($executablePath, $temporaryRootPath);

        $this->strip = $strip;
        $this->quality = $quality;
        $this->progressive = $progressive;
    }

    /**
     * @deprecated All post-processor setters have been deprecated in 1.10.0 for removal in 2.0. You must only use the
     *             class's constructor to set the property state.
     *
     * @param int $maxQuality
     *
     * @return JpegOptimPostProcessor
     */
    public function setMax($maxQuality)
    {
        $this->triggerSetterMethodDeprecation(__METHOD__);
        $this->quality = $maxQuality;

        return $this;
    }

    /**
     * @deprecated All post-processor setters have been deprecated in 1.10.0 for removal in 2.0. You must only use the
     *             class's constructor to set the property state.
     *
     * @param bool $progressive
     *
     * @return JpegOptimPostProcessor
     */
    public function setProgressive($progressive)
    {
        $this->triggerSetterMethodDeprecation(__METHOD__);
        $this->progressive = $progressive;

        return $this;
    }

    /**
     * @deprecated All post-processor setters have been deprecated in 1.10.0 for removal in 2.0. You must only use the
     *             class's constructor to set the property state.
     *
     * @param bool $strip
     *
     * @return JpegOptimPostProcessor
     */
    public function setStripAll($strip)
    {
        $this->triggerSetterMethodDeprecation(__METHOD__);
        $this->strip = $strip;

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

        if (isset($options['strip_all']) ? $options['strip_all'] : $this->strip) {
            $builder->add('--strip-all');
        }

        if (isset($options['max'])) {
            @trigger_error(sprintf('The "max" option was deprecated in 1.10.0 and will be removed in 2.0. '.
                'Instead, use the "quality" option.'), E_USER_DEPRECATED);

            if (isset($options['quality'])) {
                throw new InvalidOptionException('the "max" and "quality" options cannot both be set', $options);
            }

            $options['quality'] = $options['max'];
        }

        if ($quality = isset($options['quality']) ? $options['quality'] : $this->quality) {
            if (!in_array($options['quality'], range(0, 100))) {
                throw new InvalidOptionException('the "quality" option must be an int between 0 and 100', $options);
            }

            $builder->add(sprintf('--max=%d', $quality));
        }

        if (isset($options['progressive']) ? $options['progressive'] : $this->progressive) {
            $builder->add('--all-progressive');
        } else {
            $builder->add('--all-normal');
        }

        return $builder;
    }
}
