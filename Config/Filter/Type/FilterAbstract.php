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

/**
 * @codeCoverageIgnore
 */
abstract class FilterAbstract implements FilterInterface
{
    const NAME = self::NAME;

    public function getName(): string
    {
        return self::NAME;
    }
}
