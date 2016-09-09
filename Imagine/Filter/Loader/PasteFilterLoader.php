<?php

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;

class PasteFilterLoader implements LoaderInterface
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
        $x = isset($options['start'][0]) ? $options['start'][0] : null;
        $y = isset($options['start'][1]) ? $options['start'][1] : null;

        $destImage = $this->imagine->open($this->rootPath.'/'.$options['image']);

        return $image->paste($destImage, new Point($x, $y));
    }
}
