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

final class Thumbnail implements FilterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $size;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var bool
     */
    private $allowUpscale;

    /**
     * @var string
     */
    private $filter;

    /**
     * @param string      $name
     * @param array       $size         size parameters {width, height}
     * @param string|null $mode
     * @param bool|null   $allowUpscale
     * @param string|null $filter
     */
    public function __construct(
        string $name,
        array $size,
        string $mode = null,
        bool $allowUpscale = null,
        string $filter = null
    ) {
        $this->name = $name;
        $this->size = $size;
        $this->mode = $mode;
        $this->allowUpscale = $allowUpscale;
        $this->filter = $filter;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSize(): ?array
    {
        return $this->size;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function isAllowUpscale(): ?bool
    {
        return $this->allowUpscale;
    }

    public function getFilter(): ?string
    {
        return $this->filter;
    }
}
