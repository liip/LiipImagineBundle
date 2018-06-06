<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Factory\Config\Filter;

use Liip\ImagineBundle\Config\Filter\Type\Thumbnail;
use Liip\ImagineBundle\Config\FilterInterface;
use Liip\ImagineBundle\Factory\Config\Filter\Argument\SizeFactory;
use Liip\ImagineBundle\Factory\Config\FilterFactoryInterface;

final class ThumbnailFactory implements FilterFactoryInterface
{
    const NAME = 'thumbnail';

    /**
     * @var SizeFactory
     */
    private $sizeFactory;

    public function __construct(SizeFactory $sizeFactory)
    {
        $this->sizeFactory = $sizeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options): FilterInterface
    {
        $size = $this->sizeFactory->createFromOptions($options);
        $mode = isset($options['mode']) ? $options['mode'] : null;
        $allowUpscale = isset($options['allow_upscale']) ? $options['allow_upscale'] : null;
        $filter = isset($options['filter']) ? $options['filter'] : null;

        return new Thumbnail(self::NAME, $size, $mode, $allowUpscale, $filter);
    }
}
