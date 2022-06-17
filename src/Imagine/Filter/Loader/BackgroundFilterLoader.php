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

use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;

class BackgroundFilterLoader implements LoaderInterface
{
    protected ImagineInterface $imagine;

    public function __construct(ImagineInterface $imagine)
    {
        $this->imagine = $imagine;
    }

    public function load(ImageInterface $image, array $options = []): ImageInterface
    {
        $background = $image->palette()->color(
            $options['color'] ?? '#fff',
            $options['transparency'] ?? null
        );
        $topLeft = new Point(0, 0);
        $size = $image->getSize();

        if (\array_key_exists('size', $options)) {
            $width = $options['size'][0] ?? null;
            $height = $options['size'][1] ?? null;

            $position = $options['position'] ?? 'center';
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
                case 'centerright':
                    $x = $width - $image->getSize()->getWidth();
                    $y = ($height - $image->getSize()->getHeight()) / 2;
                    break;
                case 'center':
                    $x = ($width - $image->getSize()->getWidth()) / 2;
                    $y = ($height - $image->getSize()->getHeight()) / 2;
                    break;
                case 'centerleft':
                    $x = 0;
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
            }

            $size = new Box($width, $height);
            $topLeft = new Point($x, $y);
        }

        return $this->imagine->create($size, $background)->paste($image, $topLeft);
    }
}
