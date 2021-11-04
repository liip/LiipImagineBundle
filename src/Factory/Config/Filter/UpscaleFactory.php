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

use Liip\ImagineBundle\Config\Filter\Type\Upscale;
use Liip\ImagineBundle\Config\FilterInterface;
use Liip\ImagineBundle\Factory\Config\Filter\Argument\SizeFactory;
use Liip\ImagineBundle\Factory\Config\FilterFactoryInterface;

/**
 * @internal
 * @codeCoverageIgnore
 */
final class UpscaleFactory implements FilterFactoryInterface
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
        return Upscale::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options): FilterInterface
    {
        $min = $this->sizeFactory->createFromOptions($options, 'min');
        $by = isset($options['by']) ? (float) $options['by'] : null;

        return new Upscale($min, $by);
    }
}
