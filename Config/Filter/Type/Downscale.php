<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Config\Filter\Type;

use Liip\ImagineBundle\Config\Filter\Argument\Size;

/**
 * @codeCoverageIgnore
 */
final class Downscale extends FilterAbstract
{
    const NAME = 'downscale';

    /**
     * @var Size
     */
    private $max;

    /**
     * @var float
     */
    private $by;

    /**
     * @param float|null $by sets the "ratio multiple" which initiates a proportional scale operation computed by multiplying all image sides by this value
     */
    public function __construct(Size $max = null, float $by = null)
    {
        $this->max = $max;
        $this->by = $by;
    }

    public function getMax(): ?Size
    {
        return $this->max;
    }

    public function getBy(): ?float
    {
        return $this->by;
    }
}
