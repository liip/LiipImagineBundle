<?php

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
     * {@inheritDoc}
     */
    public function load(ImageInterface $image, array $options = array())
    {
        if (list($method, $parameter) = each($options)) {
            $filter = new RelativeResize($method, $parameter);

            return $filter->apply($image);
        }

        throw new InvalidArgumentException('Expected method/parameter pair, none given');
    }
}
