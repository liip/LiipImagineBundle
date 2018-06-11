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

use Liip\ImagineBundle\Config\Filter\Type\Crop;
use Liip\ImagineBundle\Config\FilterInterface;
use Liip\ImagineBundle\Factory\Config\Filter\Argument\PointFactory;
use Liip\ImagineBundle\Factory\Config\Filter\Argument\SizeFactory;
use Liip\ImagineBundle\Factory\Config\FilterFactoryInterface;

/**
 * @internal
 * @codeCoverageIgnore
 */
final class CropFactory implements FilterFactoryInterface
{
    /**
     * @var SizeFactory
     */
    private $sizeFactory;

    /**
     * @var PointFactory
     */
    private $pointFactory;

    public function __construct(SizeFactory $sizeFactory, PointFactory $pointFactory)
    {
        $this->sizeFactory = $sizeFactory;
        $this->pointFactory = $pointFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return Crop::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options): FilterInterface
    {
        return new Crop(
            $this->pointFactory->createFromOptions($options, 'start'),
            $this->sizeFactory->createFromOptions($options)
        );
    }
}
