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
    /**
     * @var string
     */
    protected $dimensionKey;

    /**
     * @var string
     */
    protected $ratioKey;

    /**
     * @var bool
     */
    protected $absoluteRatio;

    public function __construct($dimensionKey = 'dim', $ratioKey = 'to', $absoluteRatio = true)
    {
        $this->dimensionKey = $dimensionKey;
        $this->ratioKey = $ratioKey;
        $this->absoluteRatio = $absoluteRatio;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ImageInterface $image, array $options = [])
    {
        if (!isset($options[$this->dimensionKey]) && !isset($options[$this->ratioKey])) {
            throw new \InvalidArgumentException("Missing $this->dimensionKey or $this->ratioKey option.");
        }

        $size = $image->getSize();
        $origWidth = $size->getWidth();
        $origHeight = $size->getHeight();
        $ratio = 1;

        if (isset($options[$this->ratioKey])) {
            $ratio = $this->absoluteRatio ? $options[$this->ratioKey] : $this->calcAbsoluteRatio($options[$this->ratioKey]);
        } elseif (isset($options[$this->dimensionKey])) {
            $size = $options[$this->dimensionKey];
            $width = isset($size[0]) ? $size[0] : null;
            $height = isset($size[1]) ? $size[1] : null;

            $widthRatio = $width / $origWidth;
            $heightRatio = $height / $origHeight;

            if (null === $width || null === $height) {
                $ratio = max($widthRatio, $heightRatio);
            } else {
                $ratio = ('min' === $this->dimensionKey) ? max($widthRatio, $heightRatio) : min($widthRatio, $heightRatio);
            }
        }

        if ($this->isImageProcessable($ratio)) {
            $filter = new Resize(new Box(round($origWidth * $ratio), round($origHeight * $ratio)));

            return $filter->apply($image);
        }

        return $image;
    }

    protected function calcAbsoluteRatio($ratio)
    {
        return $ratio;
    }

    protected function isImageProcessable($ratio)
    {
        return true;
    }
}
