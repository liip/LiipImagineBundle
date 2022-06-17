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

/**
 * pngquant post-processor, for optimal, web-safe, lossy png compression
 * This requires a recent version of pngquant (so 2.3 or higher?)
 * See pngqaunt.org if you are unable to find a binary package for your distribution.
 *
 * @see https://pngquant.org/
 *
 * @author Alex Wilson <a@ax.gy>
 */
class PngquantPostProcessor extends AbstractPostProcessor
{
    /**
     * @var int|int[] Quality to pass to pngquant
     */
    protected $quality;

    /**
     * @param int|int[] $quality
     */
    public function __construct(string $executablePath = '/usr/bin/pngquant', $quality = [80, 100])
    {
        parent::__construct($executablePath);

        $this->quality = $quality;
    }

    /**
     * @throws ProcessFailedException
     */
    public function process(BinaryInterface $binary, array $options = []): BinaryInterface
    {
        if (!$this->isBinaryTypePngImage($binary)) {
            return $binary;
        }

        $arguments = $this->getProcessArguments($options);
        $arguments[] = '-';
        $process = $this->createProcess($arguments, $options);
        $process->setInput($binary->getContent());
        $process->run();

        if (!$this->isSuccessfulProcess($process, [0, 98, 99], [])) {
            throw new ProcessFailedException($process);
        }

        return new Binary($process->getOutput(), $binary->getMimeType(), $binary->getFormat());
    }

    /**
     * @param array<string, string|int|bool> $options
     *
     * @return string[]
     */
    private function getProcessArguments(array $options = []): array
    {
        $arguments = [$this->executablePath];

        if ($quality = $options['quality'] ?? $this->quality) {
            if (!\is_array($quality)) {
                $quality = [0, (int) $quality];
            }

            if (1 === \count($quality)) {
                array_unshift($quality, 0);
            }

            if ($quality[0] > $quality[1]) {
                throw new InvalidOptionException('the "quality" option cannot have a greater minimum value value than maximum quality value', $options);
            }

            if (!\in_array($quality[0], range(0, 100), true) || !\in_array($quality[1], range(0, 100), true)) {
                throw new InvalidOptionException('the "quality" option value(s) must be an int between 0 and 100', $options);
            }

            $arguments[] = '--quality';
            $arguments[] = sprintf('%d-%d', $quality[0], $quality[1]);
        }

        if (\array_key_exists('speed', $options) && null !== $options['speed']) {
            if (!\in_array($options['speed'], range(1, 11), true)) {
                throw new InvalidOptionException('the "speed" option must be an int between 1 and 11', $options);
            }

            $arguments[] = '--speed';
            $arguments[] = $options['speed'];
        }

        if (\array_key_exists('dithering', $options) && null !== $options['dithering']) {
            if (false === $options['dithering']) {
                $arguments[] = '--nofs';
            } elseif ($options['dithering'] >= 0 && $options['dithering'] <= 1) {
                $arguments[] = '--floyd';
                $arguments[] = $options['dithering'];
            } elseif (true !== $options['dithering']) {
                throw new InvalidOptionException('the "dithering" option must be a float between 0 and 1 or a bool', $options);
            }
        }

        return $arguments;
    }
}
