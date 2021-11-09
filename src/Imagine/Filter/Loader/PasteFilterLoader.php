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

use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;

class PasteFilterLoader implements LoaderInterface
{
    private ImagineInterface $imagine;

    private string $projectDir;

    public function __construct(ImagineInterface $imagine, string $projectDir)
    {
        $this->imagine = $imagine;
        $this->projectDir = $projectDir;
    }

    /**
     * @see \Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface::load()
     */
    public function load(ImageInterface $image, array $options = []): ImageInterface
    {
        $x = isset($options['start'][0]) ? $options['start'][0] : null;
        $y = isset($options['start'][1]) ? $options['start'][1] : null;

        $destImage = $this->imagine->open($this->projectDir.'/'.$options['image']);

        return $image->paste($destImage, new Point($x, $y));
    }
}
