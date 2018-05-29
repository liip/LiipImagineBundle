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
     * @param string $name
     * @param array $size
     * @param string|null $mode
     * @param bool|null $allowUpscale
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

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return string|null
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return bool|null
     */
    public function isAllowUpscale()
    {
        return $this->allowUpscale;
    }

    /**
     * @return string|null
     */
    public function getFilter()
    {
        return $this->filter;
    }
}
