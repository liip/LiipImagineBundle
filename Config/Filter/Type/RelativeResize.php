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

/**
 * @codeCoverageIgnore
 */
final class RelativeResize extends FilterAbstract
{
    const NAME = 'relative_resize';

    /**
     * @var float
     */
    private $heighten;

    /**
     * @var float
     */
    private $widen;

    /**
     * @var float
     */
    private $increase;

    /**
     * @var float
     */
    private $scale;

    public function __construct(
        float $heighten = null,
        float $widen = null,
        float $increase = null,
        float $scale = null
    ) {
        $this->heighten = $heighten;
        $this->widen = $widen;
        $this->increase = $increase;
        $this->scale = $scale;
    }

    public function getHeighten(): ?float
    {
        return $this->heighten;
    }

    public function getWiden(): ?float
    {
        return $this->widen;
    }

    public function getIncrease(): ?float
    {
        return $this->increase;
    }

    public function getScale(): ?float
    {
        return $this->scale;
    }
}
