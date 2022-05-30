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
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CwebpPostProcessor extends AbstractPostProcessor
{
    /**
     * Specify the compression factor for RGB channels between **0** and **100**. The default is **75**.
     *
     * In case of lossy compression , a small factor produces a smaller file with lower quality. Best quality is
     * achieved by using a value of **100**.
     *
     * In case of lossless compression (specified by the **-lossless** option), a small factor enables faster
     * compression speed, but produces a larger file. Maximum compression is achieved by using a value of **100**.
     *
     * @var int
     */
    protected $q;

    /**
     * Specify the compression factor for alpha compression between **0** and **100**. Lossless compression of alpha is
     * achieved using a value of **100**, while the lower values result in a lossy compression.
     *
     * @var int
     */
    protected $alphaQ;

    /**
     * Specify the compression method to use. This parameter controls the trade off between encoding speed and the
     * compressed file size and quality. Possible values range from **0** to **6**. When higher values are used, the
     * encoder will spend more time inspecting additional encoding possibilities and decide on the quality gain. Lower
     * value can result in faster processing time at the expense of larger file size and lower compression quality.
     *
     * @var int
     */
    protected $m;

    /**
     * Specify the predictive filtering method for the alpha plane. One of **none**, **fast** or **best**, in
     * increasing complexity and slowness order. Internally, alpha filtering is performed using four possible
     * predictions (none, horizontal, vertical, gradient). The **best** mode will try each mode in turn and pick the
     * one which gives the smaller size. The **fast** mode will just try to form an a priori guess without testing all
     * modes.
     *
     * @var string
     */
    protected $alphaFilter;

    /**
     * Specify the algorithm used for alpha compression: **0** or **1**. Algorithm **0** denotes no compression, **1**
     * uses WebP lossless format for compression.
     *
     * @var int
     */
    protected $alphaMethod;

    /**
     * Preserve RGB values in transparent area. The default is off, to help compressibility.
     *
     * @var bool
     */
    protected $exact;

    /**
     * An array of metadata to copy from the input to the output if present. Valid values: **all**, **none**, **exif**,
     * **icc**, **xmp**.
     *
     * Note that each input format may not support all combinations.
     *
     * @var string[]
     */
    protected $metadata;

    /**
     * @var OptionsResolver
     */
    private $resolver;

    /**
     * @param string[] $metadata
     */
    public function __construct(
        string $executablePath = '/usr/bin/cwebp',
        string $temporaryRootPath = null,
        int $q = null,
        int $alphaQ = null,
        int $m = null,
        string $alphaFilter = null,
        int $alphaMethod = null,
        bool $exact = null,
        array $metadata = []
    ) {
        parent::__construct($executablePath, $temporaryRootPath);

        $this->q = $q;
        $this->alphaQ = $alphaQ;
        $this->m = $m;
        $this->alphaFilter = $alphaFilter;
        $this->alphaMethod = $alphaMethod;
        $this->exact = $exact;
        $this->metadata = $metadata;
        $this->resolver = new OptionsResolver();

        $this->configureOptions($this->resolver);
    }

    public function process(BinaryInterface $binary, array $options = []): BinaryInterface
    {
        if (!$this->isBinaryTypeWebpImage($binary)) {
            return $binary;
        }

        $file = $this->writeTemporaryFile($binary, $options, 'imagine-post-processor-cwebp');
        $arguments = $this->getProcessArguments($options);
        $arguments[] = $file;
        $arguments[] = '-o';
        $arguments[] = '-';
        $process = $this->createProcess($arguments, $options);

        $process->run();

        if (!$this->isSuccessfulProcess($process)) {
            unlink($file);

            throw new ProcessFailedException($process);
        }

        $result = new Binary($process->getOutput(), $binary->getMimeType(), $binary->getFormat());

        unlink($file);

        return $result;
    }

    protected function isBinaryTypeWebpImage(BinaryInterface $binary): bool
    {
        return $this->isBinaryTypeMatch($binary, ['image/webp']);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('q', $this->q)
            ->setAllowedTypes('q', ['null', 'int'])
            ->setAllowedValues('q', static function ($value) {
                if (null === $value) {
                    return true;
                }

                return $value >= 0 && $value <= 100;
            });

        $resolver
            ->setDefault('alphaQ', $this->alphaQ)
            ->setAllowedTypes('alphaQ', ['null', 'int'])
            ->setAllowedValues('alphaQ', static function ($value) {
                if (null === $value) {
                    return true;
                }

                return $value >= 0 && $value <= 100;
            });

        $resolver
            ->setDefault('m', $this->m)
            ->setAllowedTypes('m', ['null', 'int'])
            ->setAllowedValues('m', static function ($value) {
                if (null === $value) {
                    return true;
                }

                return $value >= 0 && $value <= 6;
            });

        $resolver
            ->setDefault('alphaFilter', $this->alphaFilter)
            ->setAllowedTypes('alphaFilter', ['null', 'string'])
            ->setAllowedValues('alphaFilter', [null, 'none', 'fast', 'best']);

        $resolver
            ->setDefault('alphaMethod', $this->alphaMethod)
            ->setAllowedTypes('alphaMethod', ['null', 'int'])
            ->setAllowedValues('alphaMethod', [null, 0, 1]);

        $resolver
            ->setDefault('exact', $this->exact)
            ->setAllowedTypes('exact', ['null', 'bool']);

        $resolver
            ->setDefault('metadata', $this->metadata)
            ->setAllowedTypes('metadata', ['null', 'array'])
            ->setAllowedValues('metadata', static function ($value) {
                if (null === $value) {
                    return true;
                }

                foreach ($value as $metadata) {
                    if (!\in_array($metadata, ['all', 'none', 'exif', 'icc', 'xmp'], true)) {
                        return false;
                    }
                }

                return true;
            });
    }

    /**
     * @param array<mixed> $options
     *
     * @return string[]
     */
    protected function getProcessArguments(array $options = []): array
    {
        $options = $this->resolver->resolve($options);
        $arguments = [$this->executablePath];

        if ($q = $options['q']) {
            $arguments[] = '-q';
            $arguments[] = $q;
        }

        if ($alphaQ = $options['alphaQ']) {
            $arguments[] = '-alpha_q';
            $arguments[] = $alphaQ;
        }

        if ($m = $options['m']) {
            $arguments[] = '-m';
            $arguments[] = $m;
        }

        if ($alphaFilter = $options['alphaFilter']) {
            $arguments[] = '-alpha_filter';
            $arguments[] = $alphaFilter;
        }

        $alphaMethod = $options['alphaMethod'];
        if (null !== $alphaMethod) {
            $arguments[] = '-alpha_method';
            $arguments[] = $alphaMethod;
        }

        if ($options['exact']) {
            $arguments[] = '-exact';
        }

        if ($metadata = $options['metadata']) {
            $arguments[] = '-metadata';
            $arguments[] = implode(',', $metadata);
        }

        return $arguments;
    }
}
