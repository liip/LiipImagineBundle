<?php

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Image\Point;
use Imagine\Filter\Basic\Paste;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;

class PasteFilterLoader implements LoaderInterface
{
    public function __construct(ImagineInterface $imagine, $rootPath)
    {
        $this->imagine = $imagine;
        $this->rootPath = $rootPath;
    }

    public function load(ImageInterface $image, array $options = array())
    {
        list($x, $y) = $options['start'];
        $destImage = $this->imagine->open($this->rootPath.'/../'.$options['image']);

        $filter = new Paste($destImage, new Point($x, $y));
        $image = $filter->apply($image);

        return $image;
    }
}
