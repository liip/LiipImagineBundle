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

use Liip\ImagineBundle\Config\FilterInterface;

final class RelativeResize implements FilterInterface
{
    /**
     * @var string
     */
    private $name;

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

    /**
     * @param string     $name
     * @param float|null $heighten
     * @param float|null $widen
     * @param float|null $increase
     * @param float|null $scale
     */
    public function __construct(
        string $name,
        float $heighten = null,
        float $widen = null,
        float $increase = null,
        float $scale = null
    ) {
        $this->name = $name;
        $this->heighten = $heighten;
        $this->widen = $widen;
        $this->increase = $increase;
        $this->scale = $scale;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return float|null
     */
    public function getHeighten()
    {
        return $this->heighten;
    }

    /**
     * @return float|null
     */
    public function getWiden()
    {
        return $this->widen;
    }

    /**
     * @return float|null
     */
    public function getIncrease()
    {
        return $this->increase;
    }

    /**
     * @return float|null
     */
    public function getScale()
    {
        return $this->scale;
    }
}
