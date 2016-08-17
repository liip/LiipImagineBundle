<?php

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Filter\Basic\Resize;
use Imagine\Image\ImageInterface;
use Imagine\Image\Box;

/**
 * Scale filter.
 *
 * @author Devi Prasad <https://github.com/deviprsd21>
 */
class ScaleFilterLoader implements LoaderInterface
{
    public function __construct ($dimentionKey = 'dim', $ratioKey = 'to', $absoluteRatio = true) {
        $this->dimentionKey = $dimentionKey;
        $this->ratioKey = $ratioKey;
        $this->absoluteRatio = $absoluteRatio;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ImageInterface $image, array $options = array())
    {
        if (!isset($options[$this->dimentionKey]) && !isset($options[$this->ratioKey])) {
            throw new \InvalidArgumentException("Missing $this->dimentionKey or $this->ratioKey option.");
        }

        $size = $image->getSize();
        $origWidth = $size->getWidth();
        $origHeight = $size->getHeight();

        if (isset($options[$this->ratioKey])) {
            $ratio = $this->absoluteRatio ? $options[$this->ratioKey] : $this->calcAbsoluteRatio($options[$this->ratioKey]);
        } else if (isset($options[$this->dimentionKey])) {
            list($width, $height) = $options[$this->dimentionKey];

            $widthRatio = $width / $origWidth;
            $heightRatio = $height / $origHeight;

            if (null == $width || null == $height) {
                $ratio = max($widthRatio, $heightRatio);
            } else {
                $ratio = min($widthRatio, $heightRatio);
            }
        }        

        if ($this->isImageProcessable($ratio)) {
            $filter = new Resize(new Box(round($origWidth * $ratio), round($origHeight * $ratio)));

            return $filter->apply($image);
        }

        return $image;
    }

    protected function calcAbsoluteRatio ($ratio) {
        return $ratio;
    }

    protected function isImageProcessable ($ratio) {
        return true;
    }
}
