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

class AvifPostProcessor extends AbstractPostProcessor
{
    /**
     * @var int|null
     */
    protected $quality;

    /**
     * @var int|null
     */
    protected $speed;

    /**
     * @var int|null
     */
    protected $jobs;

    /**
     * @var OptionsResolver
     */
    private $resolver;

    public function __construct(
        string $executablePath = '/usr/bin/avifenc',
        ?string $temporaryRootPath = null,
        ?int $quality = null,
        ?int $speed = null,
        ?int $jobs = null
    ) {
        parent::__construct($executablePath, $temporaryRootPath);

        $this->quality = $quality;
        $this->speed = $speed;
        $this->jobs = $jobs;
        $this->resolver = new OptionsResolver();

        $this->configureOptions($this->resolver);
    }

    public function process(BinaryInterface $binary, array $options = []): BinaryInterface
    {
        if (!$this->isBinaryTypeSupported($binary)) {
            return $binary;
        }

        $input = $this->writeTemporaryFile($binary, $options, 'imagine-post-processor-avif-input');
        if (false === mb_strpos(basename($input), '.')) {
            $inputWithExtension = $input.$this->getExtensionFromMimeType($binary->getMimeType());
            if (rename($input, $inputWithExtension)) {
                $input = $inputWithExtension;
            }
        }

        $output = $this->acquireTemporaryFilePath($options, 'imagine-post-processor-avif-output').'.avif';

        $arguments = $this->getProcessArguments($options);
        $arguments[] = $input;
        $arguments[] = $output;
        $process = $this->createProcess($arguments, $options);

        $process->run();

        if (!$this->isSuccessfulProcess($process)) {
            unlink($input);
            @unlink($output);

            throw new ProcessFailedException($process);
        }

        $result = new Binary(file_get_contents($output), 'image/avif', 'avif');

        unlink($input);
        unlink($output);

        return $result;
    }

    private function getExtensionFromMimeType(string $mimeType): string
    {
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return '.jpg';
            case 'image/png':
                return '.png';
            case 'image/avif':
                return '.avif';
            default:
                return '';
        }
    }

    protected function isBinaryTypeSupported(BinaryInterface $binary): bool
    {
        return $this->isBinaryTypeMatch($binary, ['image/avif', 'image/jpeg', 'image/jpg', 'image/png']);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('quality', $this->quality)
            ->setAllowedTypes('quality', ['null', 'int'])
            ->setAllowedValues('quality', static function ($value) {
                if (null === $value) {
                    return true;
                }

                return $value >= 0 && $value <= 100;
            });

        $resolver
            ->setDefault('speed', $this->speed)
            ->setAllowedTypes('speed', ['null', 'int'])
            ->setAllowedValues('speed', static function ($value) {
                if (null === $value) {
                    return true;
                }

                return $value >= 0 && $value <= 10;
            });

        $resolver
            ->setDefault('jobs', $this->jobs)
            ->setAllowedTypes('jobs', ['null', 'int'])
            ->setAllowedValues('jobs', static function ($value) {
                if (null === $value) {
                    return true;
                }

                return $value >= 0;
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

        if (null !== $options['quality']) {
            $arguments[] = '-q';
            $arguments[] = $options['quality'];
        }

        if (null !== $options['speed']) {
            $arguments[] = '--speed';
            $arguments[] = $options['speed'];
        }

        if (null !== $options['jobs']) {
            $arguments[] = '--jobs';
            $arguments[] = $options['jobs'];
        }

        return $arguments;
    }
}
