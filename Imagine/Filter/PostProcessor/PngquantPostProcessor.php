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
     * @var string Quality to pass to pngquant
     */
    protected $quality;

    /**
     * @param string $executablePath
     * @param array  $quality
     */
    public function __construct($executablePath = '/usr/bin/pngquant', $quality = [80, 100])
    {
        parent::__construct($executablePath);

        $this->quality = $quality;
    }

    /**
     * @deprecated All post-processor setters have been deprecated in 2.2 for removal in 3.0. You must only use the
     *             class's constructor to set the property state.
     *
     * @param string $quality
     *
     * @return PngquantPostProcessor
     */
    public function setQuality($quality)
    {
        $this->triggerSetterMethodDeprecation(__METHOD__);
        $this->quality = $quality;

        return $this;
    }

    /*
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
     * @param string[] $options
     *
     * @return string[]
     */
    private function getProcessArguments(array $options = []): array
    {
        $arguments = [$this->executablePath];

        if ($quality = $options['quality'] ?? $this->quality) {
            if (\is_string($quality) && false !== mb_strpos($quality, '-')) {
                @trigger_error('Passing the "quality" option as a string was deprecated in 2.2 and '.
                    'will be removed in 3.0. Instead, pass wither an integer representing the max value or an array '.
                    'representing the minimum and maximum values.', E_USER_DEPRECATED);

                $quality = array_map(function ($q) {
                    return (int) $q;
                }, explode('-', $quality));
            }

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

        if (isset($options['speed'])) {
            if (!\in_array($options['speed'], range(1, 11), true)) {
                throw new InvalidOptionException('the "speed" option must be an int between 1 and 11', $options);
            }

            $arguments[] = '--speed';
            $arguments[] = $options['speed'];
        }

        if (isset($options['dithering'])) {
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
