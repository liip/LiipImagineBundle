<?php

namespace Liip\ImagineBundle\Imagine\Filter;

use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;

class PasteFilter extends AbstractConfigurableFilter
{
    protected $imagine;
    protected $rootPath;

    public function __construct(ImagineInterface $imagine, $rootPath)
    {
        $this->imagine = $imagine;
        $this->rootPath = $rootPath;
    }

    public function apply(ImageInterface $image)
    {
        list($x, $y) = $this->options['start'];
        $destImage = $this->imagine->open($this->rootPath . DIRECTORY_SEPARATOR . $this->options['image']);

        return $image->paste($destImage, new Point($x, $y));
    }
}
