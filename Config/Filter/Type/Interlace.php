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
final class Interlace extends FilterAbstract
{
    const NAME = 'interlace';

    /**
     * @var string
     */
    private $mode;

    public function __construct(string $mode)
    {
        $this->mode = $mode;
    }

    public function getMode(): string
    {
        return $this->mode;
    }
}
