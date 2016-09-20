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

class WatermarkFilterLoader implements LoaderInterface
{
    /**
     * @var ImagineInterface
     */
    protected $imagine;

    /**
     * @var string
     */
    protected $rootPath;

    public function __construct(ImagineInterface $imagine, $rootPath)
    {
        $this->imagine = $imagine;
        $this->rootPath = $rootPath;
    }

    /**
     * @see Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface::load()
     */
    public function load(ImageInterface $image, array $options = array())
    {
        $options += array(
            'size' => null,
            'position' => 'center',
        );

        if (substr($options['size'], -1) == '%') {
            $options['size'] = substr($options['size'], 0, -1) / 100;
        }

        $watermark = $this->imagine->open($this->rootPath.'/'.$options['image']);

        $size = $image->getSize();
        $watermarkSize = $watermark->getSize();

        // If 'null': Downscale if needed
        if (!$options['size'] && ($size->getWidth() < $watermarkSize->getWidth() || $size->getHeight() < $watermarkSize->getHeight())) {
            $options['size'] = 1.0;
        }

        if ($options['size']) {
            $factor = $options['size'] * min($size->getWidth() / $watermarkSize->getWidth(), $size->getHeight() / $watermarkSize->getHeight());

            $watermark->resize(new Box($watermarkSize->getWidth() * $factor, $watermarkSize->getHeight() * $factor));
            $watermarkSize = $watermark->getSize();
        }

        switch ($options['position']) {
            case 'topleft':
                $x = 0;
                $y = 0;
                break;
            case 'top':
                $x = ($size->getWidth() - $watermarkSize->getWidth()) / 2;
                $y = 0;
                break;
            case 'topright':
                $x = $size->getWidth() - $watermarkSize->getWidth();
                $y = 0;
                break;
            case 'left':
                $x = 0;
                $y = ($size->getHeight() - $watermarkSize->getHeight()) / 2;
                break;
            case 'center':
                $x = ($size->getWidth() - $watermarkSize->getWidth()) / 2;
                $y = ($size->getHeight() - $watermarkSize->getHeight()) / 2;
                break;
            case 'right':
                $x = $size->getWidth() - $watermarkSize->getWidth();
                $y = ($size->getHeight() - $watermarkSize->getHeight()) / 2;
                break;
            case 'bottomleft':
                $x = 0;
                $y = $size->getHeight() - $watermarkSize->getHeight();
                break;
            case 'bottom':
                $x = ($size->getWidth() - $watermarkSize->getWidth()) / 2;
                $y = $size->getHeight() - $watermarkSize->getHeight();
                break;
            case 'bottomright':
                $x = $size->getWidth() - $watermarkSize->getWidth();
                $y = $size->getHeight() - $watermarkSize->getHeight();
                break;
            default:
                throw new \InvalidArgumentException("Unexpected position '{$options['position']}'");
                break;
        }

        return $image->paste($watermark, new Point($x, $y));
    }
}
