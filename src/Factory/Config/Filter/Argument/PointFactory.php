<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Factory\Config\Filter\Argument;

use Liip\ImagineBundle\Config\Filter\Argument\Point;
use Liip\ImagineBundle\Exception\InvalidArgumentException;

/**
 * @internal
 * @codeCoverageIgnore
 */
final class PointFactory
{
    public function create($x = null, $y = null): Point
    {
        return new Point($x, $y);
    }

    public function createFromOptions(array $options, string $propertyName): Point
    {
        if (!isset($options[$propertyName])) {
            return new Point();
        }

        if (!\is_array($options[$propertyName])) {
            throw new InvalidArgumentException(sprintf('Invalid value for %s provided, array expected.', $propertyName));
        }

        $x = $options[$propertyName][0] ?? null;
        $y = $options[$propertyName][1] ?? null;

        return new Point($x, $y);
    }
}
