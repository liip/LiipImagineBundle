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

use Imagine\Filter\Basic\Crop;
use Imagine\Filter\Basic\Resize;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Fixed size filter.
 *
 * @author Robbe Clerckx <https://github.com/robbeman>
 */
class FixedFilterLoader implements LoaderInterface
{
    /**
     * @return ImageInterface
     */
    public function load(ImageInterface $image, array $options = [])
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setRequired(['width', 'height']);
        $options = $optionsResolver->resolve($options);

        // get the original image size and create a crop box
        $size = $image->getSize();
        $box = new Box($options['width'], $options['height']);

        // determine scale
        if ($size->getWidth() / $size->getHeight() > $box->getWidth() / $box->getHeight()) {
            $size = $size->heighten($box->getHeight());
        } else {
            $size = $size->widen($box->getWidth());
        }

        // define filters
        $resize = new Resize($size);
        $origin = new Point(
            floor(($size->getWidth() - $box->getWidth()) / 2),
            floor(($size->getHeight() - $box->getHeight()) / 2)
        );
        $crop = new Crop($origin, $box);

        // apply filters to image
        $image = $resize->apply($image);
        $image = $crop->apply($image);

        return $image;
    }
}
