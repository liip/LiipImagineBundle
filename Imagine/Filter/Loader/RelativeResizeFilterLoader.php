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

use Imagine\Exception\InvalidArgumentException;
use Imagine\Image\ImageInterface;
use Liip\ImagineBundle\Imagine\Filter\RelativeResize;

/**
 * Loader for this bundle's relative resize filter.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class RelativeResizeFilterLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ImageInterface $image, array $options = array())
    {
        foreach ($options as $method => $parameter) {
            $filter = new RelativeResize($method, $parameter);

            return $filter->apply($image);
        }

        throw new InvalidArgumentException('Expected method/parameter pair, none given');
    }
}
