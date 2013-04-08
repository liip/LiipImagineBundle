<?php
namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Image\Color;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;

class BackgroundFilterLoader implements LoaderInterface
{
    public function __construct(ImagineInterface $imagine)
    {
        $this->imagine = $imagine;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ImageInterface $image, array $options = array())
    {
        $background = new Color(isset($options['color']) ? $options['color'] : '#fff');
        $topLeft = new Point(0, 0);
        $canvas = $this->imagine->create($image->getSize(), $background);

        return $canvas->paste($image, $topLeft);
    }
}
