<?php

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Filter\Basic\Strip;
use Imagine\Image\ImageInterface;

class StripFilterLoader implements LoaderInterface
{
    public function load(ImageInterface $image, array $options = array())
    {
        $filter = new Strip();
        $image = $filter->apply($image);

        return $image;
    }
}
