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
final class Thumbnail extends FilterAbstract
{
    const NAME = 'thumbnail';

    /**
     * @var Size
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

    public function __construct(
        Size $size,
        string $mode = null,
        bool $allowUpscale = null,
        string $filter = null
    ) {
        $this->size = $size;
        $this->mode = $mode;
        $this->allowUpscale = $allowUpscale;
        $this->filter = $filter;
    }

    public function getSize(): Size
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
