<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Filter\Basic\Resize;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;

/**
 * Scale filter.
 *
 * @author Devi Prasad <https://github.com/deviprsd21>
 */
class ScaleFilterLoader implements LoaderInterface
{
    private string $dimensionKey;

    private string $ratioKey;

    private bool $absoluteRatio;

    public function __construct(string $dimensionKey = 'dim', string $ratioKey = 'to', bool $absoluteRatio = true)
    {
        $this->dimensionKey = $dimensionKey;
        $this->ratioKey = $ratioKey;
        $this->absoluteRatio = $absoluteRatio;
    }

    public function load(ImageInterface $image, array $options = []): ImageInterface
    {
        $size = $image->getSize();
        $origWidth = $size->getWidth();
        $origHeight = $size->getHeight();
        $ratio = 1;

        if (\array_key_exists($this->ratioKey, $options)) {
            $ratio = $this->absoluteRatio ? $options[$this->ratioKey] : $this->calcAbsoluteRatio($options[$this->ratioKey]);
        } elseif (\array_key_exists($this->dimensionKey, $options)) {
            $size = $options[$this->dimensionKey];
            $width = $size[0] ?? null;
            $height = $size[1] ?? null;

            $widthRatio = $width / $origWidth;
            $heightRatio = $height / $origHeight;

            if (null === $width || null === $height) {
                $ratio = max($widthRatio, $heightRatio);
            } else {
                $ratio = ('min' === $this->dimensionKey) ? max($widthRatio, $heightRatio) : min($widthRatio, $heightRatio);
            }
        } else {
            throw new \InvalidArgumentException("Missing $this->dimensionKey or $this->ratioKey option.");
        }

        if ($this->isImageProcessable($ratio)) {
            $filter = new Resize(new Box((int) round($origWidth * $ratio), (int) round($origHeight * $ratio)));

            return $filter->apply($image);
        }

        return $image;
    }

    protected function calcAbsoluteRatio(float $ratio): float
    {
        return $ratio;
    }

    protected function isImageProcessable(float $ratio): bool
    {
        return true;
    }
}
