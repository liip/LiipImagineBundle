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
use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Liip\ImagineBundle\Imagine\Cache\Helper\PathHelper;

class PasteFilterLoader implements LoaderInterface
{
    /**
     * @var ImagineInterface
     */
    protected $imagine;

    /**
     * @var string
     */
    protected $projectDir;

    public function __construct(ImagineInterface $imagine, $projectDir)
    {
        $this->imagine = $imagine;
        $this->projectDir = $projectDir;
    }

    /**
     * @see LoaderInterface::load
     *
     * @return ImageInterface|static
     */
    public function load(ImageInterface $image, array $options = [])
    {
        $x = isset($options['start'][0]) ? $options['start'][0] : null;
        $y = isset($options['start'][1]) ? $options['start'][1] : null;

        $imagePath = (string) $options['image'];
        if (PathHelper::containsTraversal($imagePath)) {
            throw new InvalidArgumentException(\sprintf('The "image" option "%s" must not contain ".." segments, it has to be inside the project directory.', $imagePath));
        }

        $destImage = $this->imagine->open($this->projectDir.'/'.$imagePath);

        return $image->paste($destImage, new Point($x, $y));
    }
}
