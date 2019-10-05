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

use Liip\ImagineBundle\Config\Filter\Type\Background;
use Liip\ImagineBundle\Config\FilterInterface;
use Liip\ImagineBundle\Factory\Config\Filter\Argument\SizeFactory;
use Liip\ImagineBundle\Factory\Config\FilterFactoryInterface;

/**
 * @internal
 * @codeCoverageIgnore
 */
final class BackgroundFactory implements FilterFactoryInterface
{
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
        return Background::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options): FilterInterface
    {
        $color = $options['color'] ?? null;
        $transparency = $options['transparency'] ?? null;
        $position = $options['position'] ?? null;
        $size = $this->sizeFactory->createFromOptions($options);

        return new Background($color, $transparency, $position, $size);
    }
}
