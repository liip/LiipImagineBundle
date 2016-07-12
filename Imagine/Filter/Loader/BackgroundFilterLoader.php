<?php

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;

class BackgroundFilterLoader implements LoaderInterface
{
    /**
     * @var ImagineInterface
     */
    protected $imagine;

    public function __construct(ImagineInterface $imagine)
    {
        $this->imagine = $imagine;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ImageInterface $image, array $options = array())
    {
        $background = $image->palette()->color(
            isset($options['color']) ? $options['color'] : '#fff',
            isset($options['transparency']) ? $options['transparency'] : null
        );
        $topLeft = new Point(0, 0);
        $size = $image->getSize();

        if (isset($options['size'])) {
            list($width, $height) = $options['size'];

            $position = isset($options['position']) ? $options['position'] : 'center';
            switch ($position) {
                case 'topleft':
                    $x = 0;
                    $y = 0;
                    break;
                case 'top':
                    $x = ($width - $image->getSize()->getWidth()) / 2;
                    $y = 0;
                    break;
                case 'topright':
                    $x = $width - $image->getSize()->getWidth();
                    $y = 0;
                    break;
                case 'left':
                    $x = 0;
                    $y = ($height - $image->getSize()->getHeight()) / 2;
                    break;
                case 'center':
                    $x = ($width - $image->getSize()->getWidth()) / 2;
                    $y = ($height - $image->getSize()->getHeight()) / 2;
                    break;
                case 'right':
                    $x = $width - $image->getSize()->getWidth();
                    $y = ($height - $image->getSize()->getHeight()) / 2;
                    break;
                case 'bottomleft':
                    $x = 0;
                    $y = $height - $image->getSize()->getHeight();
                    break;
                case 'bottom':
                    $x = ($width - $image->getSize()->getWidth()) / 2;
                    $y = $height - $image->getSize()->getHeight();
                    break;
                case 'bottomright':
                    $x = $width - $image->getSize()->getWidth();
                    $y = $height - $image->getSize()->getHeight();
                    break;
                default:
                    throw new \InvalidArgumentException("Unexpected position '{$position}'");
                    break;
            }

            $size = new Box($width, $height);
            $topLeft = new Point($x, $y);
        }

        $canvas = $this->imagine->create($size, $background);

        return $canvas->paste($image, $topLeft);
    }
}
