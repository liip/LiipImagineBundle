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

use Liip\ImagineBundle\Config\Filter\Type\Paste;
use Liip\ImagineBundle\Config\FilterInterface;
use Liip\ImagineBundle\Factory\Config\Filter\Argument\PointFactory;
use Liip\ImagineBundle\Factory\Config\FilterFactoryInterface;

final class PasteFactory implements FilterFactoryInterface
{
    const NAME = 'auto_rotate';

    /**
     * @var PointFactory
     */
    private $pointFactory;

    public function __construct(PointFactory $pointFactory)
    {
        $this->pointFactory = $pointFactory;
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
        return new Paste(self::NAME, $this->pointFactory->createFromOptions($options, 'start'));
    }
}
